<?php

namespace Umanit\TranslationBundle\Admin\Extension;

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
     * @inheritdoc
     *
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        $locale = $this->defaultAdminLocale ?: $admin->getRequest()->getLocale();

        if (!$admin->id($object)) {
            $object->setLocale($locale);
        }
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     *
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('locale', ChoiceFilter::class, [
            'advanced_filter' => false,
            'show_filter'     => false,
        ], 'choice', [
            'choices' => array_combine($this->locales, $this->locales),
        ]);
    }

    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper)
    {
        if (!$listMapper->has('locale')) {
            $listMapper->add('locale');
        }

        if (!$listMapper->has('translations')) {
            $listMapper->add('translations', 'array', [
                'template' => '@UmanitTranslation/Admin/CRUD/list_translations.html.twig',
            ]);
        }

        if (!$listMapper->has('_action')) {
            $listMapper->add('_action', null, [
                'actions' => [
                    'edit'   => [],
                    'show'   => [],
                    'delete' => [],
                ],
            ]);
        }

        // Update the edit template
        $options         = $listMapper->get('_action')->getOption('actions');
        $options['edit'] = ['template' => '@UmanitTranslation/Admin/CRUD/list__action_edit.html.twig'];
        $listMapper->get('_action')->setOption('actions', $options);
    }

    /**
     * @inheritdoc.
     *
     * @param AdminInterface  $admin
     * @param RouteCollection $collection
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        // Add the tranlate route
        $collection->add('translate', $admin->getRouterIdParameter() . '/translate/{newLocale}', [
            '_controller' => 'UmanitTranslationBundle:TranslatableCRUD:translate',
        ]);
    }

    /**
     * @inheritdoc
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
}
