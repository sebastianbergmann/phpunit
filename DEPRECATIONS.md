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

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                       | Since  | Replacement                                                                                                                                                                                                                                                                                                                                                                                                                       |
|-------------------------------------------------------------------|-----------------------------------|--------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [#6052](https://github.com/sebastianbergmann/phpunit/issues/6052) | `Assert::isType()`                | 11.5.0 | Use `isArray()`, `isBool()`, `isCallable()`, `isFloat()`, `isInt()`, `isIterable()`, `isNull()`, `isNumeric()`, `isObject()`, `isResource()`, `isClosedResource()`, `isScalar()`, or `isString()` instead                                                                                                                                                                                                                         |
| [#6055](https://github.com/sebastianbergmann/phpunit/issues/6055) | `Assert::assertContainsOnly()`    | 11.5.0 | Use `assertContainsOnlyArray()`, `assertContainsOnlyBool()`, `assertContainsOnlyCallable()`, `assertContainsOnlyFloat()`, `assertContainsOnlyInt()`, `assertContainsOnlyIterable()`, `assertContainsOnlyNumeric()`, `assertContainsOnlyObject()`, `assertContainsOnlyResource()`, `assertContainsOnlyClosedResource()`, `assertContainsOnlyScalar()`, or `assertContainsOnlyString()` instead                                     |
| [#6055](https://github.com/sebastianbergmann/phpunit/issues/6055) | `Assert::assertNotContainsOnly()` | 11.5.0 | Use `assertContainsNotOnlyArray()`, `assertContainsNotOnlyBool()`, `assertContainsNotOnlyCallable()`, `assertContainsNotOnlyFloat()`, `assertContainsNotOnlyInt()`, `assertContainsNotOnlyIterable()`, `assertContainsNotOnlyNumeric()`, `assertContainsNotOnlyObject()`, `assertContainsNotOnlyResource()`, `assertContainsNotOnlyClosedResource()`, `assertContainsNotOnlyScalar()`, or `assertContainsNotOnlyString()` instead |
| [#6059](https://github.com/sebastianbergmann/phpunit/issues/6059) | `Assert::containsOnly()`          | 11.5.0 | Use `containsOnlyArray()`, `containsOnlyBool()`, `containsOnlyCallable()`, `containsOnlyFloat()`, `containsOnlyInt()`, `containsOnlyIterable()`, `containsOnlyNumeric()`, `containsOnlyObject()`, `containsOnlyResource()`, `containsOnlyClosedResource()`, `containsOnlyScalar()`, or `containsOnlyString()`  instead                                                                                                            |

### Running Tests

| Issue                                                             | Description                       | Since  | Replacement                                          |
|-------------------------------------------------------------------|-----------------------------------|--------|------------------------------------------------------|
| [#6240](https://github.com/sebastianbergmann/phpunit/issues/6240) | `--dont-report-useless-tests` CLI option | 12.2.3 | Use `--dont-report-useless-tests` CLI option instead |
