<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;

use Liinkiing\GraphQLMakerBundle\Utils\Str;
use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class MakeGraphQLResolver extends CustomMaker
{

    private $phpResolverTemplatePath = __DIR__ . '/../Resources/skeleton/Resolver.tpl.php';

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:resolver';
    }

    /**
     * Configure the command: set description, input arguments, options, etc.
     *
     * By default, all arguments will be asked interactively. If you want
     * to avoid that, use the $inputConfig->setArgumentAsNonInteractive() method.
     *
     * @param Command $command
     * @param InputConfiguration $inputConfig
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Creates a new GraphQL PHP resolver')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a resolver name (e.g. <fg=yellow>UserPosts</>)'))
        ;
    }

    /**
     * Configure any library dependencies that your maker requires.
     *
     * @param DependencyBuilder $dependencies
     */
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies
            ->addClassDependency(OverblogGraphQLBundle::class, 'overblog/graphql-bundle');
    }

    /**
     * Called after normal code generation: allows you to do anything.
     *
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     * @throws \Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $this->io = $io;
        $name = $input->getArgument('name');

        if ($name) {
            $name = str_replace('Resolver', '', $name);
            $fcn = Str::normalizeNamespace($this->rootNamespace."\\Resolver\\" . ucfirst($name) . 'Resolver');
            $generator->generateClass(
                $fcn,
                $this->phpResolverTemplatePath
            );

            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }

    }

}
