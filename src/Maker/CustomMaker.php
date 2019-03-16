<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;


use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class CustomMaker extends AbstractMaker
{
    protected const AVAILABLE_OBJECT_TYPES = ['object', 'interface', 'enum', 'union'];
    protected const AVAILABLE_FIELD_TYPES = ['String', 'Int', 'Float', 'Boolean', 'ID'];
    /**
     * @var SymfonyStyle $io
     */
    protected $io;
    protected $typesPath = 'config/graphql/types/';
    protected $rootDir;
    protected $rootNamespace;

    public function __construct(string $rootNamespace, string $rootDir)
    {
        $this->rootNamespace = $rootNamespace;
        $this->rootDir = $rootDir;
    }

    protected function printAvailableTypes(): void
    {
        $this->io->writeln('<fg=green>Available types (</><fg=yellow>excluding custom scalars</><fg=green>)</>');
        foreach (self::AVAILABLE_FIELD_TYPES as $FIELD_TYPE) {
            $this->io->writeln("  - <fg=yellow>$FIELD_TYPE</> (or <fg=yellow>[$FIELD_TYPE]</>)");
        }
        $this->io->writeln('');
    }

    protected function askForNextField(bool $isFirstField): ?array
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

        [$type, $nullable] = $this->askFieldType($defaultType);
        $description = $this->askQuestion('Field description?', "I am the $name description");

        return compact('name', 'type', 'description', 'nullable');
    }

    /**
     * @param string $question
     * @param mixed|null $default
     * @param array|null $autocompleterValues
     * @param callable|null $validator
     * @return mixed
     */
    protected function askQuestion(string $question, $default = null, ?array $autocompleterValues = null, ?callable $validator = null)
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
    protected function askConfirmationQuestion(string $question, $default = true, ?callable $validator = null)
    {
        $question = new ConfirmationQuestion($question, $default);
        if ($validator) {
            $question->setValidator($validator);
        }
        return $this->io->askQuestion($question);
    }

    protected function writelnSpaced($message): void
    {
        $this->io->writeln('');
        $this->io->writeln($message);
        $this->io->writeln('');
    }

    public function parseTemplate(string $templatePath, array $parameters = []): string
    {
        ob_start();
        extract($parameters, EXTR_SKIP);
        include $templatePath;

        return ob_get_clean();
    }

    protected function askFieldType(string $defaultType): array
    {
        $type = null;
        $allValidTypes = self::AVAILABLE_FIELD_TYPES;
        while (null === $type) {
            $question = new Question('Field type (enter <comment>?</comment> to see all types)', $defaultType);
            $question->setAutocompleterValues($allValidTypes);
            $type = str_replace('!', '', $this->io->askQuestion($question));

            if ('?' === $type) {
                $this->printAvailableTypes();
                $this->io->writeln('');

                $type = null;
            }
        }
        $nullable = $this->io->confirm('Can this field be nullable', false);

        return [$type, $nullable];
    }
}
