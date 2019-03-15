<?php

namespace Liinkiing\GraphQLMakerBundle\Maker;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeGraphQLQuery extends CustomMaker
{

    private $templatePath = __DIR__ . '/../Resources/skeleton/Query.fragment.tpl.php';
    private $targetPath = 'config/graphql/types/Query.types.yaml';

    private function getTargetPath(): string
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . $this->targetPath;
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
        if (!file_exists($this->getTargetPath())) {
            $this->targetPath = 'config/graphql/types/Query.types.yml';
            if (!file_exists($this->getTargetPath())) {
                throw new FileNotFoundException('You must create your Query type file before adding new queries');
            }
        }

        $command
            ->setDescription('Creates a new GraphQL query')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a query name (e.g. <fg=yellow>allPosts</>)'));
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
        $name = $input->getArgument('name');

        if ($name) {
            $description = $this->askQuestion(
                'What is your query description?',
                "Get $name"
            );
            $type = $this->askQuestion(
                'What is your query type?'
            );
            $typeNullable = $this->askConfirmationQuestion(
                'Is your query type nullable?', false
            );

            $content = file_get_contents($this->getTargetPath());

            $content .= $this->parseTemplate(
                $this->templatePath,
                compact('name', 'description', 'type', 'typeNullable')
            );
            $generator->dumpFile(
                $this->targetPath,
                $content
            );
            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }

    }

}
