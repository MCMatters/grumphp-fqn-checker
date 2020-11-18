## Grumphp Fqn Checker Task

### Installation

```bash
composer require mcmatters/grumphp-fqn-checker
```

### Usage

Add next lines to your `grumphp.yml`:

```yaml
grumphp:
    tasks:
      fqn_checker: ~

services:
    McMatters\Grumphp\FqnChecker\FqnCheckerTask:
        tags:
            - { name: grumphp.task, task: fqn_checker }
```
