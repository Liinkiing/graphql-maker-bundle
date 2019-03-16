<?php

namespace Liinkiing\GraphQLMakerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Omar Jbara <omar.jbara2@gmail.com>
 */
class GraphQLMakerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('makers.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($container->getDefinitions() as $definition) {
            if ($definition->hasTag('maker.graphql_command')) {
                $definition->replaceArgument(
                    0,
                    // Make sure we have two trailing slashes, to prevent issues in YAML to FQCN conversion
                    // for `resolve` value in generated queries and mutations
                    str_replace('\\', '\\\\', $config['root_namespace'])
                );
            }
        }
    }

    public function getAlias(): string
    {
        return Configuration::NAME;
    }
}
