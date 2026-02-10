# Deprecations

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                               | Since  | Replacement                                                                                                          |
|-------------------------------------------------------------------|-----------------------------------------------------------|--------|----------------------------------------------------------------------------------------------------------------------|
| [#6461](https://github.com/sebastianbergmann/phpunit/issues/6461) | `TestCase::any()`                                         | 12.5.5 | Use a test stub instead or configure a real invocation count expectation                                             |
| [#6505](https://github.com/sebastianbergmann/phpunit/issues/6505) | Calling `atLeast()` with an argument that is not positive | 13.0.2 | Use a positive argument instead                                                                                      |
| [#6507](https://github.com/sebastianbergmann/phpunit/issues/6507) | Support for using `with*()` without `expects()`           | 13.0.2 | Either configure an expected invocation count using `expects()`or use a test stub without the `with*()` call instead |
