<?php

declare(strict_types=1);

namespace McMatters\Grumphp\FqnChecker;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use McMatters\FqnChecker\Console\Command\RunCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function implode;

use const PHP_EOL;

/**
 * Class FqnCheckerTask
 *
 * @package McMatters\Grumphp\FqnChecker
 */
class FqnCheckerTask implements TaskInterface
{
    /**
     * @var \GrumPHP\Task\Config\TaskConfigInterface
     */
    private $config;

    /**
     * @return \Symfony\Component\OptionsResolver\OptionsResolver
     */
    public static function getConfigurableOptions(): OptionsResolver
    {
        return new OptionsResolver();
    }

    /**
     * @param \GrumPHP\Task\Config\TaskConfigInterface $config
     *
     * @return \GrumPHP\Task\TaskInterface
     */
    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    /**
     * @return \GrumPHP\Task\Config\TaskConfigInterface
     */
    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    /**
     * @param \GrumPHP\Task\Context\ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * @param \GrumPHP\Task\Context\ContextInterface $context
     *
     * @return \GrumPHP\Runner\TaskResultInterface
     *
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
                implode(PHP_EOL, $errors)
            );
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param \GrumPHP\Collection\FilesCollection $files
     *
     * @return array
     *
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
