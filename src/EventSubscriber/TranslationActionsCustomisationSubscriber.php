<?php

declare(strict_types=1);

namespace Umanit\TranslationBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

class TranslationActionsCustomisationSubscriber implements EventSubscriberInterface
{
    private const TRANSLATION_EXISTS_ICON = 'fa-solid fa-check text-success';
    private const ADD_TRANSLATION_ICON = 'fa-solid fa-plus';

    private ?EntityManagerInterface $em;
    private ?EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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

        $this->repository = $this->em->getRepository($event->getAdminContext()->getEntity()->getFqcn());
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

                $translationExists = in_array($translateInto, $object->getTranslations());
                $url = parse_url($action->getLinkUrl());
                parse_str($url['query'], $query);

                // Update URL to edit existing translation
                if ($translationExists) {
                    $translation = $this->repository->findOneBy([
                        'tuuid'  => $object->getTuuid(),
                        'locale' => $translateInto,
                    ]);

                    // Should not happen
                    if (
                        null === $translation ||
                        !array_key_exists('crudAction', $query) ||
                        !array_key_exists('entityId', $query)
                    ) {
                        continue;
                    }

                    $query['crudAction'] = Action::EDIT;
                    $query['entityId'] = $translation->getId();
                    $url['query'] = http_build_query($query);

                    $action->setLinkUrl(sprintf('%s://%s%s?%s', $url['scheme'], $url['host'], $url['path'], $url['query']));
                }

                // Set icon
                $action->setIcon($translationExists ? self::TRANSLATION_EXISTS_ICON : self::ADD_TRANSLATION_ICON);

                // Add locale to URL
                $action->setLinkUrl(sprintf('%s&locale=%s', $action->getLinkUrl(), $translateInto));

                // Removes the 'btn' class added by the ActionFactory
                $action->setCssClass(str_replace(' btn', '', $action->getCssClass()));
            }
        }
    }
}