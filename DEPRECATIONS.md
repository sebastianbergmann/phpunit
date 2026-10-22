# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                       | Since  | Replacement                                                             |
|-------------------------------------------------------------------|---------------------------------------------------|--------|-------------------------------------------------------------------------|
| [#6537](https://github.com/sebastianbergmann/phpunit/issues/6537) | `id()` and `after()` for mock object expectations | 13.1.0 |                                                                         |
| [#6560](https://github.com/sebastianbergmann/phpunit/issues/6560) | `expectExceptionMessage()`                        | 13.2.0 | `expectExceptionMessageIsOrContains()`                                  |
| [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686) | `Constraint::failureDescriptionInContext()`       | 13.4.0 | Override `Constraint::negatedFailureDescription()` in your constraint   |
| [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686) | `LogicalNot::negate()`                            | 13.4.0 | Override `Constraint::negatedToString()` in your constraint             |

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                               | Since  | Replacement                                                                                                          |
|-------------------------------------------------------------------|-----------------------------------------------------------|--------|----------------------------------------------------------------------------------------------------------------------|
| [#6461](https://github.com/sebastianbergmann/phpunit/issues/6461) | `TestCase::any()`                                         | 12.5.5 | Use a test stub instead or configure a real invocation count expectation                                             |
| [#6505](https://github.com/sebastianbergmann/phpunit/issues/6505) | Calling `atLeast()` with an argument that is not positive | 13.0.2 | Use a positive argument instead                                                                                      |
| [#6507](https://github.com/sebastianbergmann/phpunit/issues/6507) | Support for using `with*()` without `expects()`           | 13.0.2 | Either configure an expected invocation count using `expects()`or use a test stub without the `with*()` call instead |

### Running Tests

| Issue                                                             | Description                                                   | Since  | Replacement                                                     |
|-------------------------------------------------------------------|---------------------------------------------------------------|--------|-----------------------------------------------------------------|
|                                                                   | `--cache-result` CLI option                                   | 13.3.0 | Use `--record-test-run-history` instead                         |
|                                                                   | `--do-not-cache-result` CLI option                            | 13.3.0 | Use `--do-not-record-test-run-history` instead                  |
|                                                                   | `cacheResult` XML configuration attribute                     | 13.3.0 | Use `recordTestRunHistory` instead                              |

### Extending PHPUnit

| Issue                                                             | Description                                                   | Since  | Replacement                                                     |
|-------------------------------------------------------------------|---------------------------------------------------------------|--------|-----------------------------------------------------------------|
|                                                                   | `TextUI\Configuration\Configuration::cacheResult()`           | 13.3.0 | Use `recordTestRunHistory()` instead                            |
|                                                                   | `TextUI\Configuration\Configuration::testResultCacheFile()`   | 13.3.0 | Use `testRunHistoryFile()` instead                              |
