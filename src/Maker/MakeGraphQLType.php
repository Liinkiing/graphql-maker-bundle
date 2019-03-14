<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;

use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeGraphQLType extends AbstractMaker
{
    const AVAILABLE_OBJECT_TYPES = ['object', 'interface', 'enum', 'union'];
    const AVAILABLE_FIELD_TYPES = ['String', 'Int', 'Float', 'Boolean', 'ID'];
    /**
     * @var SymfonyStyle $io
     */
    private $io;

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
        $this->io = $io;
        $name = ucfirst($input->getArgument('name'));
        if ($name) {
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
                "config/graphql/types/{$name}.types.yaml",
                __DIR__ . '/../Resources/skeleton/Type.types.tpl.php',
                [
                    'name' => $name,
                    'type' => $type,
                    'description' => $description,
                    'hasFields' => $hasFields,
                    'fields' => $fields,
                    'inherits' => array_map('trim', explode(', ', $inherits ?? '')),
                    'interfaces' => array_map('trim', explode(', ', $interfaces ?? '')),
                ]
            );

            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }
    }

    /**
     * @param string $question
     * @param mixed|null $default
     * @param callable|null $validator
     * @return mixed
     */
    private function askQuestion(string $question, $default = null, ?array $autocompleterValues = null, ?callable $validator = null)
    {
        $question = new Question($question, $default);
        if ($validator) {
            $question->setValidator($validator);
        }
        if ($autocompleterValues) {
            $question->setAutocompleterValues($autocompleterValues);
        }
        return $this->io->askQuestion($question);
    }

    /**
     * @param string $question
     * @param mixed|null $default
     * @param callable|null $validator
     * @return mixed
     */
    private function askConfirmationQuestion(string $question, $default = true, ?callable $validator = null)
    {
        $question = new ConfirmationQuestion($question, $default);
        if ($validator) {
            $question->setValidator($validator);
        }
        return $this->io->askQuestion($question);
    }

    private function askForNextField(bool $isFirstField)
    {
        $this->io->writeln('');

        if ($isFirstField) {
            $questionText = 'New field name (press <return> to stop adding fields)';
        } else {
            $questionText = 'Add another field? Enter the field name (or press <return> to stop adding fields)';
        }

        $name = $this->io->ask($questionText);

        if (!$name) {
            return null;
        }

        $defaultType = self::AVAILABLE_FIELD_TYPES[0];
        $snakeCasedField = Str::asSnakeCase($name);

        if ('id' === substr($snakeCasedField, -2)) {
            $defaultType = self::AVAILABLE_FIELD_TYPES[4];
        } elseif (0 === strpos($snakeCasedField, 'is_')) {
            $defaultType = self::AVAILABLE_FIELD_TYPES[3];
        } elseif (0 === strpos($snakeCasedField, 'has_')) {
            $defaultType = self::AVAILABLE_FIELD_TYPES[3];
        }

        $type = null;
        $allValidTypes = self::AVAILABLE_FIELD_TYPES;
        while (null === $type) {
            $question = new Question('Field type (enter <comment>?</comment> to see all types)', $defaultType);
            $question->setAutocompleterValues($allValidTypes);
            $type = $this->io->askQuestion($question);

            if ('?' === $type) {
                $this->printAvailableTypes();
                $this->io->writeln('');

                $type = null;
            } elseif (!\in_array($type, $allValidTypes, true)) {
                $this->printAvailableTypes();
                $this->io->error(sprintf('Invalid type "%s".', $type));
                $this->io->writeln('');

                $type = null;
            }
        }

        $description = $this->askQuestion('Field description?', "I am the $name description");
        $nullable = $this->io->confirm('Can this field be nullable', false);

        return compact('name', 'type', 'description', 'nullable');
    }

    private function printAvailableTypes()
    {
        $this->io->writeln('<fg=green>Available types (</><fg=yellow>excluding custom scalars</><fg=green>)</>');
        foreach (self::AVAILABLE_FIELD_TYPES as $FIELD_TYPE) {
            $this->io->writeln("  - <fg=yellow>$FIELD_TYPE</>");
        }
        $this->io->writeln('');
    }

}
