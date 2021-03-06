<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;

use Liinkiing\GraphQLMakerBundle\Utils\Str;
use Liinkiing\GraphQLMakerBundle\Utils\Validator;
use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class MakeGraphQLMutation extends CustomMaker
{

    private const ACCESS_EXAMPLES = [
        "@=hasRole('ROLE_ADMIN')",
        "@=hasAnyRole('ROLE_ADMIN', 'ROLE_USER')",
        '@=isAnonymous()',
        '@=isAuthenticated()',
        "@=hasPermission(object, 'OWNER')"
    ];
    private const EXPRESSION_LANGUAGE_DOC_URL = 'https://github.com/overblog/GraphQLBundle/blob/master/docs/definitions/expression-language.md';
    private $firstTime = false;
    private $mutationYamlTemplatePath = __DIR__ . '/../Resources/skeleton/Mutation.yaml.tpl.php';
    private $mutationTemplatePath = __DIR__ . '/../Resources/skeleton/Mutation.fragment.tpl.php';
    private $inputTemplatePath = __DIR__ . '/../Resources/skeleton/Input.types.tpl.php';
    private $payloadTemplatePath = __DIR__ . '/../Resources/skeleton/Payload.types.tpl.php';
    private $phpMutationTemplatePath = __DIR__ . '/../Resources/skeleton/Mutation.tpl.php';
    private $filename = 'Mutation.types.yaml';

    private function getTargetPath(): string
    {
        return ($this->schemaOutDir ??$this->outdir) . DIRECTORY_SEPARATOR . $this->filename;
    }

    private function getInputTargetPath(string $name): string
    {
        return ($this->schemaOutDir ??$this->outdir) . DIRECTORY_SEPARATOR . "$name.types.yaml";
    }

    private function getPayloadTargetPath(string $name): string
    {
        return ($this->schemaOutDir ??$this->outdir) . DIRECTORY_SEPARATOR . "$name.types.yaml";
    }


    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:mutation';
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
            ->setDescription('Creates a new GraphQL mutation')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a mutation name (e.g. <fg=yellow>addPost</>)'))
            ->addOption('schema', 's', InputOption::VALUE_OPTIONAL, 'Specify your GraphQL schema (e.g internal, preview, public)')
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
        $schema = $input->getOption('schema');
        $this->schemaOutDir = $this->getSchemaOutDir($schema);

        if (!file_exists($this->getTargetPath())) {
            $this->filename = 'Mutation.types.yml';
            if (!file_exists($this->getTargetPath())) {
                $this->filename = 'Mutation.types.yaml';
                $this->firstTime = true;
            }
        }
        $mutationName = $input->getArgument('name');

        if ($mutationName) {
            $description = $this->askQuestion(
                'What is your mutation description?',
                "I am the $mutationName description"
            );
            $hasAccess = $this->askConfirmationQuestion(
                'Do you want to add custom access for this mutation?', false
            );
            $access = null;
            if ($hasAccess) {
                $this->printAccessExpressionsExamples();
                $access = $this->askQuestion(
                    'Please type your access expression',
                    null,
                    null,
                    [Validator::class, 'notBlank']
                );
            }

            $content = $this->firstTime ?
                $this->parseTemplate($this->mutationYamlTemplatePath, compact('schema')) :
                file_get_contents($this->getTargetPath());

            $inputName = $this->createNameBasedOnSchema($schema, $mutationName. 'Input');
            $payloadName = $this->createNameBasedOnSchema($schema, $mutationName. 'Payload');
            $rootNamespace = Str::normalizeNamespace($this->rootNamespace);

            $content .= $this->parseTemplate(
                $this->mutationTemplatePath,
                compact('access', 'hasAccess', 'rootNamespace', 'mutationName', 'description', 'inputName', 'payloadName')
            );
            $generator->dumpFile(
                $this->getTargetPath(),
                $content
            );

            $this->writelnSpaced("Now, let's configure $inputName!");
            $this->generateMutationInput($generator, $inputName, $mutationName);

            $this->writelnSpaced("Now, let's configure $payloadName!");
            $this->generateMutationPayload($generator, $payloadName, $mutationName);
            $fcn = $rootNamespace . "\\Mutation\\" . ucfirst($mutationName) . 'Mutation';
            $generatePhpFiles = $this->askConfirmationQuestion(
                "Do you want to generate the PHP mutation <fg=yellow>$fcn</>"
            );

            if ($generatePhpFiles) {
                $generator->generateClass(
                    $fcn,
                    $this->phpMutationTemplatePath
                );
            }

            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }

    }

    private function printAccessExpressionsExamples()
    {
        $this->io->writeln('<fg=green>Custom access expressions examples</>');
        foreach (self::ACCESS_EXAMPLES as $ACCESS) {
            $this->io->writeln("  - <fg=yellow>$ACCESS</>");
        }
        $this->io->writeln('');
        $this->io->comment('More informations here: ' . self::EXPRESSION_LANGUAGE_DOC_URL . '');
    }

    protected function generateMutationInput(Generator $generator, string $inputName, $name): void
    {
        $inputDescription = $this->askQuestion(
            "<fg=yellow>$inputName</> - What is your mutation input description?",
            "Input of $name mutation"
        );

        $this->io->writeln("Now, let's add some fields to the $inputName");

        $isFirstField = true;
        $inputFields = [];
        while (true) {
            $newField = $this->askForNextField($isFirstField);
            $inputFields[] = $newField;
            $isFirstField = false;
            if (null === $newField) {
                $inputFields = array_filter($inputFields);
                break;
            }
        }

        $generator->generateFile(
            $this->getInputTargetPath($inputName),
            $this->inputTemplatePath,
            compact('inputName', 'inputFields', 'inputDescription')
        );
    }

    protected function generateMutationPayload(Generator $generator, string $payloadName, $name): void
    {
        $payloadDescription = $this->askQuestion(
            "<fg=yellow>$payloadName</> - What is your mutation payload description?",
            "Payload of $name mutation"
        );

        $this->io->writeln("Now, let's add some fields to the $payloadName");

        $isFirstField = true;
        $payloadFields = [];
        while (true) {
            $newField = $this->askForNextField($isFirstField);
            $payloadFields[] = $newField;
            $isFirstField = false;
            if (null === $newField) {
                $payloadFields = array_filter($payloadFields);
                break;
            }
        }

        $generator->generateFile(
            $this->getPayloadTargetPath($payloadName),
            $this->payloadTemplatePath,
            compact('payloadName', 'payloadFields', 'payloadDescription')
        );
    }

}
