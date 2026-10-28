# Deprecations

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                               | Since  | Replacement                                                                                                          |
|-------------------------------------------------------------------|-----------------------------------------------------------|--------|----------------------------------------------------------------------------------------------------------------------|
| [#6537](https://github.com/sebastianbergmann/phpunit/issues/6537) | `id()` and `after()` for mock object expectations         | 13.1.0 |                                                                                                                      |
| [#6560](https://github.com/sebastianbergmann/phpunit/issues/6560) | `expectExceptionMessage()`                                | 13.2.0 | `expectExceptionMessageIsOrContains()`                                                                               |

#### Miscellaneous

| Issue | Description                                                                                                       | Since  | Replacement |
|-------|-------------------------------------------------------------------------------------------------------------------|--------|-------------|
|       | Calling `markTestSkipped()` or `markTestIncomplete()` in an after-test method such as `tearDown()`                | 13.5.0 |             |
|       | Calling `markTestSkipped()` or `markTestIncomplete()` in an after-last-test method such as `tearDownAfterClass()` | 13.5.0 |             |
