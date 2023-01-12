<?php

namespace Umanit\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class UmanitTranslationExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set configuration into params
        $rootName = 'umanit_translation';
        $container->setParameter($rootName, $config);
        $this->setConfigAsParameters($container, $config, $rootName);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Conditionally load sonata_admin.yaml
        if ($container->hasExtension('sonata_admin')) {
            $loader->load('sonata_admin.yaml');
        }

        if ($container->hasExtension('umanit_doctrine_singleton')) {
            $loader->load('doctrine_singleton.yaml');
        }

        // Conditionnally override some templates from EasyAdmin
        if ($container->hasExtension('easy_admin')) {
            $thirdPartyBundlesViewFileLocator = new FileLocator(__DIR__.'/../Resources/views/bundles');

            $container->loadFromExtension('twig', [
                'paths' => [$thirdPartyBundlesViewFileLocator->locate('EasyAdminBundle') => 'EasyAdmin'],
            ]);
        }
    }

    /**
     * Add config keys as parameters.
     */
    private function setConfigAsParameters(ContainerBuilder $container, array $params, string $parent)
    {
        foreach ($params as $key => $value) {
            $name = $parent.'.'.$key;
            $container->setParameter($name, $value);

            if (is_array($value)) {
                $this->setConfigAsParameters($container, $value, $name);
            }
        }
    }
}
