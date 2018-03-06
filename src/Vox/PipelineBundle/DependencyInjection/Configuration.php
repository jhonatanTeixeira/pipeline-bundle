<?php

namespace Vox\PipelineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vox_pipeline');
        
        $rootNode->children()
            ->arrayNode('pipelines')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                    ->end()
                    ->scalarNode('style')
                        ->defaultValue('pipe')
                        ->validate()
                            ->ifNotInArray(['pipe', 'chain'])
                            ->thenInvalid("inavlid style option %s, use 'pipe', 'chain'")
                        ->end()
                    ->end()
                    ->arrayNode('subscribedEvents')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('tags')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('class')->end()
                    ->arrayNode('services')
                        ->prototype('scalar')->end()
                    ->end()
        ;

        return $treeBuilder;
    }
}
