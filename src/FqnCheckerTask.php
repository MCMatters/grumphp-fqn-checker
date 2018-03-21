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
use McMatters\FqnChecker\FqnChecker;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                (new FqnCheckerFormatter($errors))->format()
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
        $errors = [];

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            try {
                $unimported = (new FqnChecker($file->getContents()))->getUnimported();

                if (!empty($unimported)) {
                    $errors[$file->getRelativePathname()] = $unimported;
                }
            } catch (RuntimeException $e) {
                continue;
            }
        }

        return $errors;
    }
}
