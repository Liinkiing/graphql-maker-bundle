<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;

use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeGraphQLType extends CustomMaker
{

    private $typeTemplatePath = __DIR__ . '/../Resources/skeleton/Type.types.tpl.php';
    private $typeFieldsTemplatePath = __DIR__ . '/../Resources/skeleton/Type.fields.fragment.tpl.php';

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:type';
    }

    private function getTargetPath(string $name): string
    {
        return $this->outdir.DIRECTORY_SEPARATOR.$name;
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
            ->setDescription('Creates a new GraphQL type')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a type name (e.g. <fg=yellow>Post</>)'));
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
        if ($name) {
            $firstTime = !file_exists($this->getTargetPath($name.'.types.yaml'))
                && !file_exists($this->getTargetPath($name.'.types.yml'));
            $filename = "$name.types.yaml";
            if (file_exists($this->getTargetPath($name.'.types.yml'))) {
                $filename = "$name.types.yml";
            }
            if ($firstTime) {
                $type = $this->askQuestion(
                    'What is your object type? (e.g. <fg=yellow>object, interface, enum</>)',
                    'object',
                    self::AVAILABLE_OBJECT_TYPES
                );
                $description = $this->askQuestion(
                    'What is your type description?',
                    "I am the $name description!"
                );
                $inherits = $this->askQuestion(
                    'Does your type inherits any other types? (e.g. <fg=yellow>Comment, Video</> or leave it blank for none)'
                );
                $interfaces = $this->askQuestion(
                    'Does your type have any interfaces? (e.g. <fg=yellow>Node, Commentable</> or leave it blank for none)'
                );

                $hasFields = $this->askConfirmationQuestion('Do you want to add fields?');
            } else {
                $this->io->writeln("<fg=green>The <fg=yellow>$name</> type already exists! Let's add some fields.</>");
                $hasFields = true;
            }

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

            $content = $firstTime ?
                $this->parseTemplate(
                    $this->typeTemplatePath,
                    [
                        'name' => $name,
                        'type' => $type,
                        'description' => $description,
                        'hasFields' => $hasFields,
                        'fields' => $fields,
                        'inherits' => array_filter(
                            array_map('trim', explode(', ', $inherits ?? '')),
                            function ($item) { return $item !== ''; }),
                        'interfaces' => array_filter(
                            array_map('trim', explode(', ', $interfaces ?? '')),
                            function ($item) { return $item !== ''; })                ]

                ) :
                file_get_contents($this->getTargetPath($filename));

            $content .= $this->parseTemplate(
                $this->typeFieldsTemplatePath,
                compact('fields')
            );

            $generator->dumpFile(
                $this->getTargetPath($filename),
                $content
            );

            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }
    }

}
