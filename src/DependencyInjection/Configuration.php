<?php


namespace Liinkiing\GraphQLMakerBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const NAME = 'graphql_maker';
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::NAME);
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root(self::NAME);
        }
        $rootNode
            ->children()
                ->scalarNode('root_namespace')
                ->defaultValue('App\\GraphQL')
                ->validate()
                    ->ifTrue(function (string $value) {
                       return substr($value, -1) === '\\';
                    })
                    ->thenInvalid('%s does not seems to be a valid namespace')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
