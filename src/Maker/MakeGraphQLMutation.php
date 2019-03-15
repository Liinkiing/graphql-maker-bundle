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

class MakeGraphQLMutation extends CustomMaker
{

    private const ACCESS_EXAMPLES = [
        "@=hasRole('ROLE_ADMIN')",
        "@=hasAnyRole('ROLE_ADMIN', 'ROLE_USER')",
        '@=isAnonymous()',
        '@=isAuthenticated()',
        "@=hasPermission(object, 'OWNER')"
    ];
    private $templatePath = __DIR__ . '/../Resources/skeleton/Mutation.tpl.php';
    private $targetPath = 'config/graphql/types/Mutation.types.yaml';

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
        if (!file_exists($this->getTargetPath())) {
            $this->targetPath = 'config/graphql/types/Mutation.types.yml';
            if (!file_exists($this->getTargetPath())) {
                throw new FileNotFoundException('You must create your Mutation type file before adding new mutations');
            }
        }

        $command
            ->setDescription('Creates a new GraphQL mutation')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Choose a mutation name (e.g. <fg=yellow>addPost</>)'));
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
                'What is your mutation description?',
                "I am the $name description"
            );
            $hasAccess = $this->askConfirmationQuestion(
                'Do you want to add custom access for this mutation?', false
            );
            $access = null;
            if ($hasAccess) {
                $this->printAccessExpressionsExamples();
                $access = $this->askQuestion(
                    'Please type your access expression'
                );
            }

            $content = file_get_contents($this->getTargetPath());

            $content .= $this->parseTemplate(
                $this->templatePath,
                compact('access', 'hasAccess', 'name', 'description')
            );
            $generator->dumpFile(
                $this->targetPath,
                $content
            );
            $generator->writeChanges();
            $this->writeSuccessMessage($io);
        }

    }

    private function printAccessExpressionsExamples() {
        $this->io->writeln('<fg=green>Custom access expressions examples</>');
        foreach (self::ACCESS_EXAMPLES as $ACCESS) {
            $this->io->writeln("  - <fg=yellow>$ACCESS</>)");
        }
        $this->io->writeln('');
        $this->io->comment('More informations here: https://github.com/overblog/GraphQLBundle/blob/master/docs/definitions/expression-language.md');
    }

}
