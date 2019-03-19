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

class MakeGraphQLQuery extends CustomMaker
{

    private $firstTime = false;
    private $templatePath = __DIR__ . '/../Resources/skeleton/Query.fragment.tpl.php';
    private $yamlTemplatePath = __DIR__ . '/../Resources/skeleton/Query.yaml.tpl.php';
    private $phpResolverTemplatePath = __DIR__ . '/../Resources/skeleton/Mutation.tpl.php';
    private $filename = 'Query.types.yaml';

    private function getTargetPath(?string $schema): string
    {
        return ($this->schemaOutDir ?? $this->outdir) . DIRECTORY_SEPARATOR . $this->createNameBasedOnSchema($schema, $this->filename);
    }


    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:graphql:query';
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
            ->setDescription('Creates a new GraphQL query')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a query name (e.g. <fg=yellow>allPosts</>)'))
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
        $name = $input->getArgument('name');

        if (!file_exists($this->getTargetPath($schema))) {
            $this->filename = 'Query.types.yml';
            if (!file_exists($this->getTargetPath($schema))) {
                $this->filename = 'Query.types.yaml';
                $this->firstTime = true;
            }
        }

        if ($name) {
            $description = $this->askQuestion(
                'What is your query description?',
                "Get $name"
            );
            [$type, $nullable] = $this->askFieldType(self::AVAILABLE_FIELD_TYPES[0]);

            $rootNamespace = Str::normalizeNamespace($this->rootNamespace);

            $fcn = Str::normalizeNamespace($this->rootNamespace."\\Resolver\\Query\\" . ucfirst($name) . 'Resolver');
            $generatePhpFiles = $this->askConfirmationQuestion(
                "Do you want to generate the PHP resolver <fg=yellow>$fcn</>"
            );

            $content = $this->firstTime ?
                $this->parseTemplate($this->yamlTemplatePath, compact('schema')) :
                file_get_contents($this->getTargetPath($schema));

            $content .= $this->parseTemplate(
                $this->templatePath,
                compact('name', 'description', 'rootNamespace', 'type', 'nullable', 'generatePhpFiles')
            );
            $generator->dumpFile(
                $this->getTargetPath($schema),
                $content
            );


            if ($generatePhpFiles) {
                $generator->generateClass(
                    $fcn,
                    $this->phpResolverTemplatePath
                );
            }

            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }

    }

}
