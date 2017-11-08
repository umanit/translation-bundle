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
        $collection->add('translate', $admin->getRouterIdParameter() . '/translate/{newLocale}', [
            '_controller' => 'UmanitTranslationBundle:TranslatableCRUD:translate'
        ]);
    }
}
