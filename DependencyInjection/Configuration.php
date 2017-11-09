<?php

namespace Umanit\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('umanit_translation');
        $rootNode
            ->children()
            ->arrayNode('locales')
                ->prototype('scalar')->end()->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
