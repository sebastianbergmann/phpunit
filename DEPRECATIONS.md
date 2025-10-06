# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Extending PHPUnit

| Issue                                                             | Description                                 | Since  | Replacement                                     |
|-------------------------------------------------------------------|---------------------------------------------|--------|-------------------------------------------------|
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `AfterTestMethodCalled::testCaseClass()`    | 12.1.0 | `AfterTestMethodCalled::test()->className()`    |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `AfterTestMethodErrored::testCaseClass()`   | 12.1.0 | `AfterTestMethodErrored::test()->className()`   |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `AfterTestMethodFinished::testCaseClass()`  | 12.1.0 | `AfterTestMethodFinished::test()->className()`  |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `BeforeTestMethodCalled::testCaseClass()`   | 12.1.0 | `BeforeTestMethodCalled::test()->className()`   |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `BeforeTestMethodErrored::testCaseClass()`  | 12.1.0 | `BeforeTestMethodErrored::test()->className()`  |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `BeforeTestMethodFinished::testCaseClass()` | 12.1.0 | `BeforeTestMethodFinished::test()->className()` |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PreConditionCalled::testCaseClass()`       | 12.1.0 | `PreConditionCalled::test()->className()`       |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PreConditionErrored::testCaseClass()`      | 12.1.0 | `PreConditionErrored::test()->className()`      |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PreConditionFinished::testCaseClass()`     | 12.1.0 | `PreConditionFinished::test()->className()`     |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PostConditionCalled::testCaseClass()`      | 12.1.0 | `PostConditionCalled::test()->className()`      |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PostConditionErrored::testCaseClass()`     | 12.1.0 | `PostConditionErrored::test()->className()`     |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PostConditionFinished::testCaseClass()`    | 12.1.0 | `PostConditionFinished::test()->className()`    |
| [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229) | `Configuration::includeTestSuite()`         | 12.3.0 | `Configuration::includeTestSuites()`            |
| [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229) | `Configuration::excludeTestSuite()`         | 12.3.0 | `Configuration::excludeTestSuites()`            |

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                          | Since  | Replacement                                                                                                                                                                                                                                                                                                                                                                                                                       |
|-------------------------------------------------------------------|------------------------------------------------------|--------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [#6059](https://github.com/sebastianbergmann/phpunit/issues/6059) | `Assert::containsOnly()`                             | 11.5.0 | Use `containsOnlyArray()`, `containsOnlyBool()`, `containsOnlyCallable()`, `containsOnlyFloat()`, `containsOnlyInt()`, `containsOnlyIterable()`, `containsOnlyNumeric()`, `containsOnlyObject()`, `containsOnlyResource()`, `containsOnlyClosedResource()`, `containsOnlyScalar()`, or `containsOnlyString()`  instead                                                                                                            |

### Attributes

| Issue                                                             | Description                                                                                 | Since  | Replacement                                  |
|-------------------------------------------------------------------|---------------------------------------------------------------------------------------------|--------|----------------------------------------------|
| [#6246](https://github.com/sebastianbergmann/phpunit/issues/6246) | Using `#[CoversNothing]` on a test method                                                   | 12.3.0 |                                              |
| [#6284](https://github.com/sebastianbergmann/phpunit/issues/6284) | Using `#[RunClassInSeparateProcess]` on a test class                                        | 12.4.0 | Use `#[RunTestsInSeparateProcesses]` instead |
| [#6355](https://github.com/sebastianbergmann/phpunit/issues/6355) | Support for version constraint string argument without explicit version comparison operator | 12.4.0 |                                              |

### Running Tests

| Issue                                                             | Description                              | Since  | Replacement                                            |
|-------------------------------------------------------------------|------------------------------------------|--------|--------------------------------------------------------|
| [#6240](https://github.com/sebastianbergmann/phpunit/issues/6240) | `--dont-report-useless-tests` CLI option | 12.2.3 | Use `--do-not-report-useless-tests` CLI option instead |
