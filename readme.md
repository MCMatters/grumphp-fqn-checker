## GrumPHP FQN Checker Task

### Installation

```shell
composer require mcmatters/grumphp-fqn-checker
```

### Usage

Add next lines to your `grumphp.yml`:

```yaml
grumphp:
    tasks:
      fqn_checker: ~

    extensions:
      - McMatters\GrumPHPFqnChecker\FqnCheckerExtension
```
