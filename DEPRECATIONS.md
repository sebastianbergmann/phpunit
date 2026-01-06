# Deprecations

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                       | Since  | Replacement                                                              |
|-------------------------------------------------------------------|-----------------------------------|--------|--------------------------------------------------------------------------|
| [#6461](https://github.com/sebastianbergmann/phpunit/issues/6461) | `TestCase::any()`                 | 12.5.5 | Use a test stub instead or configure a real invocation count expectation |
