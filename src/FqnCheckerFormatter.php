<?php

declare(strict_types=1);

namespace McMatters\GrumPHPFqnChecker;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

use function implode;
use function strlen;
use function str_repeat;

use const PHP_EOL;

class FqnCheckerFormatter
{
    protected array $errors = [];

    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    public function format(): string
    {
        return $this->getMessage();
    }

    protected function getMessage(): string
    {
        $messages = [];
        $headers = ['Not imported', 'Lines'];
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

    protected function getRows(array $fileErrors): array
    {
        $rows = [];

        foreach ($fileErrors as $namespace => $errors) {
            foreach ($errors as $function => $lines) {
                $rows[$namespace][] = [$function, implode(', ', $lines)];
            }
        }

        return $rows;
    }

    protected function getHeading(): string
    {
        $message = 'Used not imported functions or/and constants';

        $wrap = str_repeat('*', strlen($message) + 12);

        return "{$wrap}\n**    {$message}    **\n{$wrap}";
    }
}
