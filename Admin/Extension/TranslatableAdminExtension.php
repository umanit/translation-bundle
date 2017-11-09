<?php

namespace Umanit\TranslationBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * SonataAdmin Extension.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableAdminExtension extends AbstractAdminExtension
{
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        // Add the tranlate route
        $collection->add('translate', $admin->getRouterIdParameter() . '/translate/{newLocale}', [
            '_controller' => 'UmanitTranslationBundle:TranslatableCRUD:translate'
        ]);
    }

    public function preUpdate(AdminInterface $admin, $object)
    {
        // Re-set the locale to make sure the children share the same
        $object->setLocale($object->getLocale());
        parent::preUpdate($admin, $object);
    }
}
