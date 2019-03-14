<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;

use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

class MakeGraphQLType extends AbstractMaker
{

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:gql-type';
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
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates a new GraphQL type command class')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a type name (e.g. <fg=yellow>Post</>)'));
    }

    /**
     * Configure any library dependencies that your maker requires.
     *
     * @param DependencyBuilder $dependencies
     */
    public function configureDependencies(DependencyBuilder $dependencies)
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
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $name = $input->getArgument('name');
        if ($name) {
            $question = new Question('Does your type inherits any other types? (e.g. <fg=yellow>Comment, Video</> or leave it blank for none)');
            $inherits = $io->askQuestion($question);
            $question = new Question('Does your type have any interfaces? (e.g. <fg=yellow>Node, Commentable</> or leave it blank for none)');
            $interfaces = $io->askQuestion($question);
            $generator->generateFile(
                "config/graphql/types/{$name}.types.yaml",
                __DIR__ . '/../Resources/skeleton/Type.types.tpl.php',
                [
                    'name' => $name,
                    'type' => 'object',
                    'inherits' => array_map('trim', explode(', ', $inherits ?? '')),
                    'interfaces' => array_map('trim', explode(', ', $interfaces ?? '')),
                ]
            );

            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }
    }
}
