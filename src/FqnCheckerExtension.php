<?php

declare(strict_types=1);

namespace McMatters\GrumPHPFqnChecker;

use GrumPHP\Extension\ExtensionInterface;

final class FqnCheckerExtension implements ExtensionInterface
{
    public function imports(): iterable
    {
        yield __DIR__.'/../config/fqn-checker.yml';
    }
}
