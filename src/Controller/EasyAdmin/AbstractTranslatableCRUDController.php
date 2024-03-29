<?php

declare(strict_types=1);

namespace Umanit\TranslationBundle\Controller\EasyAdmin;

use App\Admin\Filter\LocaleFilter;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\SortOrder;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\ControllerFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Environment;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Translation\EntityTranslator;

abstract class AbstractTranslatableCRUDController extends AbstractCrudController
{
    private array $locales;
    private ManagerRegistry $em;
    private EntityTranslator $entityTranslator;
    private AdminContextFactory $adminContextFactory;
    private ControllerFactory $controllerFactory;
    private Environment $twig;

    public function __construct(
        array $locales,
        ManagerRegistry $em,
        EntityTranslator $entityTranslator,
        AdminContextFactory $adminContextFactory,
        ControllerFactory $controllerFactory,
        Environment $twig
    ) {
        $this->locales = $locales;
        $this->em = $em;
        $this->entityTranslator = $entityTranslator;
        $this->adminContextFactory = $adminContextFactory;
        $this->controllerFactory = $controllerFactory;
        $this->twig = $twig;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel()->hideOnIndex();
        yield TextField::new('tuuid')->onlyOnIndex();

        $localeField = LocaleField::new('locale')->includeOnly($this->locales);

        if (Crud::PAGE_EDIT === $pageName) {
            $localeField->setDisabled();
        }

        yield $localeField;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Action::EDIT, new TranslatableMessage('admin.manage_version'))
            ->setDefaultSort(['tuuid' => SortOrder::ASC, 'id' => SortOrder::ASC])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('locale');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        if (0 < $this->locales) {
            $actions->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn(Action $action) => $action->addCssClass('border-top')
            );
        }

        foreach ($this->locales as $locale) {
            $translateAction = Action::new('translate_'.$locale, \Locale::getDisplayName($locale))
                                     ->linkToCrudAction('translate')
                                     ->setHtmlAttributes(['data-translate-into' => $locale])
            ;

            $actions
                ->add(Crud::PAGE_INDEX, $translateAction)
                ->add(Crud::PAGE_EDIT, $translateAction)
            ;
        }

        /**
         * NB: more precise configuration, object-dependent,
         * is managed by the EasyAdminActionCustomisationSubscriber
         */

        return $actions;
    }

    public function translate(AdminContext $context)
    {
        $entity = $context->getEntity()->getInstance();
        $request = $context->getRequest();
        $locale = $request->query->get('locale');
        $translatedEntity = null === $locale ? $entity : $this->em->getRepository($this::getEntityFqcn())->findOneBy([
            'tuuid' => $entity->getTuuid(),
            'locale' => $locale,
        ]);

        // Translation doesn't exist, jumpstart it
        if (null === $translatedEntity) {
            $translatedEntity = $this->entityTranslator->translate($entity, $locale);

            $this->em->getManager()->persist($translatedEntity);
            $this->em->getManager()->flush();
        }

        // Request can't be modified without creating a new context
        return parent::edit(
            $this->updateContext(
                $request,
                $translatedEntity,
                $context
            )
        );
    }

    private function updateContext(
        Request &$request,
        TranslatableInterface $entity,
        AdminContext $context
    ): AdminContext {
        // Updates entity ID in query
        $request->query->set(EA::ENTITY_ID, $entity->getId());

        // Creates new context from updated request
        $context = $this->adminContextFactory->create(
            $request,
            $this->controllerFactory->getDashboardControllerInstance($context->getDashboardControllerFqcn(), $request),
            $this->controllerFactory->getCrudControllerInstance(
                $request->query->get(EA::CRUD_CONTROLLER_FQCN),
                $request->query->get(EA::CRUD_ACTION),
                $request
            )
        );

        // Translation is done, back to the "edit" action
        $crudDto = $context->getCrud();

        $crudDto->setPageName(Crud::PAGE_EDIT);
        $crudDto->setCurrentAction(Crud::PAGE_EDIT);
        $crudDto->getActionsConfig()->setPageName(Crud::PAGE_EDIT);

        // Sets new context in request
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $context);

        // Updates context in Twig globals to allow use in templates
        // and avoid missing assets (context was already set as a global
        // before we modified it)
        $this->twig->addGlobal('ea', $context);

        return $context;
    }
}
