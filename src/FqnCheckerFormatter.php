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
        $headers = ['Unimported', 'Lines'];
        $buffer = new BufferedOutput();

        foreach ($this->errors as $file => $fileErrors) {
            foreach ($this->getRows($fileErrors) as $namespace => $rows) {
                $message = "File: {$file}".PHP_EOL."Namespace: {$namespace}".PHP_EOL;

                (new Table($buffer))->setHeaders($headers)
                    ->addRows($rows)
                    ->render();

                $messages[] = "{$message}{$buffer->fetch()}";
            }
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

        foreach ($fileErrors as $namespace =>  $errors) {
            foreach ($errors as $function => $lines) {
                $rows[$namespace][] = [$function, implode(', ', $lines)];
            }
        }

        return $rows;
    }

    /**
     * @return string
     */
    protected function getHeading(): string
    {
        $message = 'Used unimported functions or/and constants';

        $wrap = str_repeat('*', strlen($message) + 12);

        return "{$wrap}\n**    {$message}    **\n{$wrap}";
    }
}
