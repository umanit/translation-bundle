<?php

namespace Umanit\TranslationBundle\Admin\Extension;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;

/**
 * SonataAdmin Extension.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableAdminExtension extends AbstractAdminExtension
{
    /**
     * @var array
     */
    private $locales;
    /**
     * @var null
     */
    private $defaultAdminLocale;

    /**
     * TranslatableAdminExtension constructor.
     *
     * @param array $locales
     * @param null  $defaultAdminLocale
     */
    public function __construct(array $locales, $defaultAdminLocale = null)
    {
        $this->locales            = $locales;
        $this->defaultAdminLocale = $defaultAdminLocale;
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        if (!$admin->id($object)) {
            $object->setLocale($this->getEditLocale($admin));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface $admin
     * @param array          $filterValues
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues)
    {
        if ($this->defaultAdminLocale) {
            $filterValues['locale'] = [
                'value' => $this->defaultAdminLocale,
            ];
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('locale', ChoiceFilter::class, [
            'advanced_filter' => false,
        ], 'choice', [
            'choices' => array_combine($this->locales, $this->locales),
        ]);
    }

    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper)
    {
        if ($listMapper->has('translations')) {
            $listMapper
                ->get('translations')
                ->setTemplate('@UmanitTranslation/Admin/CRUD/list_translations.html.twig')
            ;
        }

        if ($listMapper->has('_action')) {
            $actions = $listMapper->get('_action')->getOption('actions');
            if ($actions && isset($actions['edit'])) {
                // Overrides edit action
                $actions['edit'] = ['template' => '@UmanitTranslation/Admin/CRUD/list__action_edit.html.twig'];
                $listMapper->get('_action')->setOption('actions', $actions);
            }
        }
    }

    /**
     * {@inheritdoc}.
     *
     * @param AdminInterface  $admin
     * @param RouteCollection $collection
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        // Add the tranlate route
        $collection->add('translate', $admin->getRouterIdParameter().'/translate/{newLocale}', [
            '_controller' => 'UmanitTranslationBundle:TranslatableCRUD:translate',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function preUpdate(AdminInterface $admin, $object)
    {
        // Re-set the locale to make sure the children share the same
        $object->setLocale($object->getLocale());
        parent::preUpdate($admin, $object);
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface      $admin
     * @param MenuItemInterface   $menu
     * @param string              $action
     * @param AdminInterface|null $childAdmin
     */
    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        // Add the locales switcher dropdown in the edit view
        if ($action === 'edit' && $admin->id($admin->getSubject())) {

            $menu->addChild('language', [
                'label'      => 'Translate ('.$this->getEditLocale($admin).')',
                'attributes' => ['dropdown' => true, 'icon' => 'fa fa-language'],
            ]);
            foreach ($this->locales as $locale) {
                $menu['language']->addChild($locale, [
                    'uri'        => $admin->generateUrl('translate', [
                        'id'        => $admin->id($admin->getSubject()),
                        'newLocale' => $locale,
                    ]),
                    'attributes' => [
                        'icon' => isset($admin->getSubject()->getTranslations()[$locale]) || $locale === $admin->getSubject()->getLocale()
                            ? 'fa fa-check'
                            : 'fa fa-plus',
                    ],
                    'current'    => $locale === $this->getEditLocale($admin),
                ]);
            }
        }
    }

    /**
     * Return the edit locale.
     *
     * @param AdminInterface $admin
     *
     * @return null|string
     */
    private function getEditLocale(AdminInterface $admin)
    {
        if ($admin->id($admin->getSubject())) {
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
