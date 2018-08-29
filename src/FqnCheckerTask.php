<?php

declare(strict_types = 1);

namespace McMatters\Grumphp\FqnChecker;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use McMatters\FqnChecker\Console\Command\RunCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use const PHP_EOL;
use function implode;

/**
 * Class FqnCheckerTask
 *
 * @package McMatters\Grumphp\FqnChecker
 */
class FqnCheckerTask implements TaskInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'fqn_checker';
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return [];
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        return new OptionsResolver();
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
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
                implode(PHP_EOL, $errors)
            );
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param FilesCollection $files
     *
     * @return array
     */
    protected function getErrors(FilesCollection $files): array
    {
        if ($files->isEmpty()) {
            return [];
        }

        $errors = [];
        $output = new BufferedOutput();

        $app = new Application();
        $app->add(new RunCommand());

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $input = new ArrayInput([
                'command' => 'fqn-checker:check',
                'path' => $file->getPath(),
            ]);

            $app->run($input, $output);

            if ($fetch = $output->fetch()) {
                $errors[$file->getRelativePathname()] = $fetch;
            }
        }

        return $errors;
    }
}
