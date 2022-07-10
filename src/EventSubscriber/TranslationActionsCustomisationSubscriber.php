<?php

declare(strict_types=1);

namespace Umanit\TranslationBundle\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Collection\ActionCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\EntityCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

class TranslationActionsCustomisationSubscriber implements EventSubscriberInterface
{
    private const TRANSLATION_EXISTS_ICON = 'fa-solid fa-check text-success';
    private const ADD_TRANSLATION_ICON = 'fa-solid fa-plus';

    public static function getSubscribedEvents(): array
    {
        return [
            AfterCrudActionEvent::class => 'conditionnallyModifyActions',
        ];
    }

    public function conditionnallyModifyActions(AfterCrudActionEvent $event)
    {
        $reflClass = new \ReflectionClass($event->getAdminContext()->getEntity()->getFqcn());

        // Only if working on translatable entities
        if (!$reflClass->implementsInterface(TranslatableInterface::class)) {
            return;
        }

        $responseParameters = $event->getResponseParameters();

        if (Crud::PAGE_INDEX === $responseParameters->get('pageName')) {
            foreach ($responseParameters->get('entities') as $entity) {
                $this->updateActionsForEntity($entity);
            }

            return;
        }

        if (Crud::PAGE_EDIT === $responseParameters->get('pageName')) {
            $this->updateActionsForEntity($responseParameters->get('entity'));
        }
    }

    private function updateActionsForEntity(EntityDto $entity)
    {
        /** @var TranslatableInterface $object */
        $object = $entity->getInstance();
        $locale = $object->getLocale();
        $actions = $entity->getActions();

        foreach ($actions as $offset => $action) {
            if (str_contains($action->getName(), 'translate')) {
                $translateInto = $action->getHtmlAttributes()['data-translate-into'];

                // Same locale, no need for the "translate" action
                if ($locale === $translateInto) {
                    $actions->offsetUnset($offset);

                    continue;
                }

                // Set icon
                $action->setIcon(in_array($translateInto, $object->getTranslations()) ? self::TRANSLATION_EXISTS_ICON : self::ADD_TRANSLATION_ICON);

                // Add locale to URL
                $action->setLinkUrl(sprintf('%s&locale=%s', $action->getLinkUrl(), $translateInto));

                // Removes the 'btn' class added by the ActionFactory
                $action->setCssClass(str_replace(' btn', '', $action->getCssClass()));
            }
        }
    }
}