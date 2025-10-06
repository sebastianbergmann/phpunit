# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Extending PHPUnit

| Issue                                                             | Description                                 | Since  | Replacement                                     |
|-------------------------------------------------------------------|---------------------------------------------|--------|-------------------------------------------------|
| [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229) | `Configuration::includeTestSuite()`         | 12.3.0 | `Configuration::includeTestSuites()`            |
| [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229) | `Configuration::excludeTestSuite()`         | 12.3.0 | `Configuration::excludeTestSuites()`            |

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

### Attributes

| Issue                                                             | Description                                                                                 | Since  | Replacement                                  |
|-------------------------------------------------------------------|---------------------------------------------------------------------------------------------|--------|----------------------------------------------|
| [#6284](https://github.com/sebastianbergmann/phpunit/issues/6284) | Using `#[RunClassInSeparateProcess]` on a test class                                        | 12.4.0 | Use `#[RunTestsInSeparateProcesses]` instead |
| [#6355](https://github.com/sebastianbergmann/phpunit/issues/6355) | Support for version constraint string argument without explicit version comparison operator | 12.4.0 |                                              |

### Running Tests

| Issue                                                             | Description                              | Since  | Replacement                                            |
|-------------------------------------------------------------------|------------------------------------------|--------|--------------------------------------------------------|
| [#6240](https://github.com/sebastianbergmann/phpunit/issues/6240) | `--dont-report-useless-tests` CLI option | 12.2.3 | Use `--do-not-report-useless-tests` CLI option instead |
