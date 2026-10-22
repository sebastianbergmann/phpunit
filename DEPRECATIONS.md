# Deprecations

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                               | Since  | Replacement                                                                                                          |
|-------------------------------------------------------------------|-----------------------------------------------------------|--------|----------------------------------------------------------------------------------------------------------------------|
| [#6507](https://github.com/sebastianbergmann/phpunit/issues/6507) | Support for using `with*()` without `expects()`           | 13.0.2 | Either configure an expected invocation count using `expects()`or use a test stub without the `with*()` call instead |
| [#6537](https://github.com/sebastianbergmann/phpunit/issues/6537) | `id()` and `after()` for mock object expectations         | 13.1.0 |                                                                                                                      |
| [#6560](https://github.com/sebastianbergmann/phpunit/issues/6560) | `expectExceptionMessage()`                                | 13.2.0 | `expectExceptionMessageIsOrContains()`                                                                               |
