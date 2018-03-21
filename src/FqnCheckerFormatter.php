<?php

declare(strict_types = 1);

namespace McMatters\Grumphp\FqnChecker;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use const PHP_EOL;
use function implode, strlen, str_repeat;

/**
 * Class FqnCheckerFormatter
 *
 * @package McMatters\Grumphp\FqnChecker
 */
class FqnCheckerFormatter
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * FqnCheckerFormatter constructor.
     *
     * @param array $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    protected function getMessage(): string
    {
        $messages = [];
        $headers = ['Function', 'Lines'];

        foreach ($this->errors as $file => $fileErrors) {
            $buffer = new BufferedOutput();

            $table = new Table($buffer);
            $table->setHeaders($headers);
            $table->addRows($this->getRows($fileErrors));
            $table->render();

            $messages[] = "File: {$file}".PHP_EOL.$buffer->fetch();
        }

        return $this->getHeading().PHP_EOL.PHP_EOL.implode(PHP_EOL, $messages);
    }

    /**
     * @param array $fileErrors
     *
     * @return array
     */
    protected function getRows(array $fileErrors): array
    {
        $rows = [];
        $combined = [];

        foreach ($fileErrors as $error) {
            $combined[$error['function']][] = $error['line'];
        }

        foreach ($combined as $function => $lines) {
            $rows[] = [$function, implode(', ', $lines)];
        }

        return $rows;
    }

    /**
     * @return string
     */
    protected function getHeading(): string
    {
        $message = 'Used unimported functions';

        $wrap = str_repeat('*', strlen($message) + 12);

        return "{$wrap}\n**    {$message}    **\n{$wrap}";
    }
}
