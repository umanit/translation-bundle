<?php

namespace Umanit\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (Kernel::VERSION_ID >= 40200) {
            $builder = new TreeBuilder('umanit_translation');
            $rootNode = $builder->getRootNode();
        } else {
            $builder = new TreeBuilder();
            $rootNode = $builder->root('umanit_translation');
        }

        $rootNode
            ->children()
                ->scalarNode('default_locale')->defaultNull()->end()
                ->arrayNode('locales')
                    ->prototype('scalar')->end()->isRequired()
                ->end()
                ->arrayNode('disabled_firewalls')->info('Defines the firewalls where the filter should be disabled (ex: admin)')
                    ->prototype('scalar')->end()->defaultValue([])
                ->end()
            ->end()
        ;

        return $builder;
    }
}
