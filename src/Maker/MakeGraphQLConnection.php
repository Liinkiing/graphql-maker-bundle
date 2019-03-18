<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;

use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeGraphQLConnection extends CustomMaker
{

    private function getTargetPath(string $name): string
    {
        return $this->outdir . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:connection';
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
            ->setDescription('Creates a new relay-compliant GraphQL connection')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a connection name (e.g. <fg=yellow>PostConnection</>)'));
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
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $this->io = $io;
        $name = ucfirst($input->getArgument('name'));
        if (!Str::hasSuffix($name, 'Connection')) {
            $name .= 'Connection';
        }
        if ($name) {
            $nodeType = $this->askQuestion(
                'What is your connection node type? (the related type in which the connection refers to)'
            );
            $nodeTypeNullable = $this->askConfirmationQuestion(
                'Is your node type nullable?', false
            );
            $hasFields = $this->askConfirmationQuestion(
                'Do you want to add custom fields to your connection?', false
            );

            $isFirstField = true;
            $fields = [];
            if ($hasFields) {
                while (true) {
                    $newField = $this->askForNextField($isFirstField);
                    $fields[] = $newField;
                    $isFirstField = false;
                    if (null === $newField) {
                        $fields = array_filter($fields);
                        break;
                    }
                }
            }

            $generator->generateFile(
                $this->getTargetPath($name),
                __DIR__ . '/../Resources/skeleton/Connection.types.tpl.php',
                [
                    'name' => $name,
                    'nodeType' => $nodeType,
                    'nodeTypeNullable' => $nodeTypeNullable,
                    'hasFields' => $hasFields,
                    'fields' => $fields
                ]
            );

            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }
    }

}
