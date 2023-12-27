<?php

declare(strict_types=1);

namespace McMatters\GrumPHPFqnChecker;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use McMatters\FqnChecker\Console\Command\RunCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function implode;

use const PHP_EOL;

class FqnCheckerTask implements TaskInterface
{
    protected TaskConfigInterface $config;

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        return ConfigOptionsResolver::fromClosure(static fn () => []);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return (
            $context instanceof GitPreCommitContext ||
            $context instanceof RunContext
        );
    }

    /**
     * @throws \Exception
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $files = $context->getFiles()->name('/(?<!\.blade)\.php$/');

        if ($files->isEmpty()) {
            return TaskResult::createSkipped($this, $context);
        }

        if ($errors = $this->getErrors($files)) {
            return TaskResult::createFailed(
                $this,
                $context,
                implode(PHP_EOL, $errors),
            );
        }

        return TaskResult::createPassed($this, $context);
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    /**
     * @throws \Exception
     */
    protected function getErrors(FilesCollection $files): array
    {
        if ($files->isEmpty()) {
            return [];
        }

        $errors = [];
        $output = new BufferedOutput();

        $app = new Application();
        $app->setAutoExit(false);
        $app->add(new RunCommand());

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($files as $file) {
            $input = new ArrayInput([
                'command' => 'fqn-checker:check',
                'path' => $file->getRealPath(),
            ]);

            $app->run($input, $output);

            if ($fetch = $output->fetch()) {
                $errors[$file->getRelativePathname()] = $fetch;
            }
        }

        return $errors;
    }
}
