<?php


namespace Liinkiing\GraphQLMakerBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const NAME = 'graphql_maker';
    public const SCHEMA_SECTION_NAME = 'schemas';
    public const DEFAULT_OUT_DIR = '%kernel.project_dir%/config/graphql/types';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::NAME);
        $rootNode = $this->createRootNode($treeBuilder, self::NAME);
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
            ->end();

        $rootNode
            ->children()
                ->scalarNode('out_dir')
                    ->defaultValue(self::DEFAULT_OUT_DIR)
                ->end()
            ->end();

        $rootNode->append($this->schemasSection());


        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function schemasSection()
    {
        $treeBuilder = new TreeBuilder(self::SCHEMA_SECTION_NAME);
        $node = $this->createRootNode($treeBuilder, self::SCHEMA_SECTION_NAME);
        $node
            ->performNoDeepMerging()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('out_dir')
                    ->defaultValue(self::DEFAULT_OUT_DIR)
        ;

        return $node;
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @param string $type
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function createRootNode(TreeBuilder $treeBuilder, string $name, string $type = 'array')
    {
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root($name, $type);
        }

        return $rootNode;
    }
}
