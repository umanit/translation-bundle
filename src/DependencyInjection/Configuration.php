<?php

namespace Umanit\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('umanit_translation');
        $rootNode = $builder->getRootNode();

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
