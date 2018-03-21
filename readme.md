## Grumphp Fqn Checker Task

### Installation

```bash
composer require mcmatters/grumphp-fqn-checker
```

### Usage

Add next lines to your `grumphp.yml`:

```yaml
parameters:
    tasks:
      fqn_checker: ~

services:
  task.fqn_checker:
    class: McMatters\Grumphp\FqnChecker\FqnCheckerTask
    tags:
      - {name: grumphp.task, config: fqn_checker}
```
