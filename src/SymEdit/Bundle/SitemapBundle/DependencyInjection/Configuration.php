<?php

namespace SymEdit\Bundle\SitemapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('symedit_sitemap');

        $allowedFreq = array(
            'always', 'hourly', 'daily', 'weekly', 'monthly',
            'yearly', 'never',
        );

        $rootNode
            ->children()
                ->arrayNode('models')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('repository')->defaultNull()->end()
                            ->scalarNode('method')->defaultValue('findAll')->end()
                            ->arrayNode('route')
                                ->children()
                                    ->scalarNode('path')->end()
                                    ->arrayNode('params')->prototype('scalar')->end()->end()
                                ->end()
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return !is_array($v); })
                                    ->then(function($v) {
                                        return array(
                                            'path' => $v
                                        );
                                    })
                                ->end()
                            ->end()
                            ->arrayNode('callbacks')->end()
                            ->scalarNode('changefreq')
                                ->validate()
                                    ->ifNotInArray($allowedFreq)
                                    ->thenInvalid('Change frequency not supported, choose one of: '.json_encode($allowedFreq))
                                ->end()
                                ->defaultValue('weekly')
                            ->end()
                            ->scalarNode('lastmod')->defaultNull()->end()
                            ->scalarNode('priority')->defaultValue('0.5')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
