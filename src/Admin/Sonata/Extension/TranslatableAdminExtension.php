<?php

namespace Umanit\TranslationBundle\Admin\Sonata\Extension;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;

class TranslatableAdminExtension extends AbstractAdminExtension
{
    private array $locales;
    private ?string $defaultAdminLocale;

    public function __construct(array $locales, ?string $defaultAdminLocale = null)
    {
        $this->locales = $locales;
        $this->defaultAdminLocale = $defaultAdminLocale;
    }

    public function alterNewInstance(AdminInterface $admin, $object): void
    {
        if (!$admin->id($object)) {
            $object->setLocale($this->getEditLocale($admin));
        }
    }

    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
        if ($this->defaultAdminLocale) {
            $filterValues['locale'] = [
                'value' => $this->defaultAdminLocale,
            ];
        }
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('locale', ChoiceFilter::class, [
            'advanced_filter' => false,
        ], [
            'choices' => array_combine($this->locales, $this->locales),
        ]);
    }

    public function configureListFields(ListMapper $list): void
    {
        if ($list->has('translations')) {
            $list
                ->get('translations')
                ->setTemplate('@UmanitTranslation/Admin/CRUD/list_translations.html.twig')
            ;
        }

        if ($list->has(ListMapper::NAME_ACTIONS)) {
            $actions = $list->get(ListMapper::NAME_ACTIONS)->getOption('actions');

            if ($actions && isset($actions['edit'])) {
                // Overrides edit action
                $actions['edit'] = ['template' => '@UmanitTranslation/Admin/CRUD/list__action_edit.html.twig'];

                $list->get(ListMapper::NAME_ACTIONS)->setOption(ListMapper::TYPE_ACTIONS, $actions);
            }
        }
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        // Add the translate route
        $collection->add('translate', $admin->getRouterIdParameter().'/translate/{newLocale}', [
            '_controller' => 'umanit_translation.controller.translatable_crudcontroller::translate',
        ]);
    }

    public function preUpdate(AdminInterface $admin, $object): void
    {
        // Re-set the locale to make sure the children share the same
        $object->setLocale($object->getLocale());

        parent::preUpdate($admin, $object);
    }

    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        string $action,
        AdminInterface $childAdmin = null
    ): void {
        // Add the locales switcher dropdown in the edit view
        if ($action === 'edit' && $admin->id($admin->getSubject())) {
            $menu->addChild('language', [
                'label'      => 'Translate ('.$this->getEditLocale($admin).')',
                'attributes' => ['dropdown' => true, 'icon' => 'fas fa-language'],
            ]);

            foreach ($this->locales as $locale) {
                $menu['language']->addChild($locale, [
                    'uri'        => $admin->generateUrl('translate', [
                        'id'        => $admin->id($admin->getSubject()),
                        'newLocale' => $locale,
                    ]),
                    'attributes' => [
                        'icon' => \in_array(
                            $locale,
                            $admin->getSubject()->getTranslations(),
                            true
                        ) || $locale === $admin->getSubject()->getLocale()
                            ? 'fas fa-check'
                            : 'fas fa-plus',
                    ],
                    'current'    => $locale === $this->getEditLocale($admin),
                ]);
            }
        }
    }

    /**
     * Return the edit locale.
     */
    private function getEditLocale(AdminInterface $admin): ?string
    {
        if ($admin->hasSubject() && $admin->id($admin->getSubject())) {
            return $admin->getSubject()->getLocale();
        }

        if ($this->defaultAdminLocale) {
            return $this->defaultAdminLocale;
        }

        if ($admin->getRequest()) {
            return $admin->getRequest()->getLocale();
        }

        return 'en';
    }
}
