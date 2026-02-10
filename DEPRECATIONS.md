# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                       | Since  | Replacement                                                              |
|-------------------------------------------------------------------|-----------------------------------|--------|--------------------------------------------------------------------------|
| [#6461](https://github.com/sebastianbergmann/phpunit/issues/6461) | `TestCase::any()`                 | 12.5.5 | Use a test stub instead or configure a real invocation count expectation |

### Extending PHPUnit

| Issue                                                             | Description                                 | Since  | Replacement                                     |
|-------------------------------------------------------------------|---------------------------------------------|--------|-------------------------------------------------|
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `AfterTestMethodCalled::testClassName()`    | 12.1.0 | `AfterTestMethodCalled::test()->className()`    |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `AfterTestMethodErrored::testClassName()`   | 12.1.0 | `AfterTestMethodErrored::test()->className()`   |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `AfterTestMethodFinished::testClassName()`  | 12.1.0 | `AfterTestMethodFinished::test()->className()`  |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `BeforeTestMethodCalled::testClassName()`   | 12.1.0 | `BeforeTestMethodCalled::test()->className()`   |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `BeforeTestMethodErrored::testClassName()`  | 12.1.0 | `BeforeTestMethodErrored::test()->className()`  |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `BeforeTestMethodFinished::testClassName()` | 12.1.0 | `BeforeTestMethodFinished::test()->className()` |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PreConditionCalled::testClassName()`       | 12.1.0 | `PreConditionCalled::test()->className()`       |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PreConditionErrored::testClassName()`      | 12.1.0 | `PreConditionErrored::test()->className()`      |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PreConditionFinished::testClassName()`     | 12.1.0 | `PreConditionFinished::test()->className()`     |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PostConditionCalled::testClassName()`      | 12.1.0 | `PostConditionCalled::test()->className()`      |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PostConditionErrored::testClassName()`     | 12.1.0 | `PostConditionErrored::test()->className()`     |
| [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140) | `PostConditionFinished::testClassName()`    | 12.1.0 | `PostConditionFinished::test()->className()`    |
| [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229) | `Configuration::includeTestSuite()`         | 12.3.0 | `Configuration::includeTestSuites()`            |
| [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229) | `Configuration::excludeTestSuite()`         | 12.3.0 | `Configuration::excludeTestSuites()`            |

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                       | Since   | Replacement                                                                                                                                                                                                                                                                                                                                                                                                                       |
|-------------------------------------------------------------------|-----------------------------------|---------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [#6052](https://github.com/sebastianbergmann/phpunit/issues/6052) | `Assert::isType()`                | 11.5.0  | Use `isArray()`, `isBool()`, `isCallable()`, `isFloat()`, `isInt()`, `isIterable()`, `isNull()`, `isNumeric()`, `isObject()`, `isResource()`, `isClosedResource()`, `isScalar()`, or `isString()` instead                                                                                                                                                                                                                         |
| [#6055](https://github.com/sebastianbergmann/phpunit/issues/6055) | `Assert::assertContainsOnly()`    | 11.5.0  | Use `assertContainsOnlyArray()`, `assertContainsOnlyBool()`, `assertContainsOnlyCallable()`, `assertContainsOnlyFloat()`, `assertContainsOnlyInt()`, `assertContainsOnlyIterable()`, `assertContainsOnlyNumeric()`, `assertContainsOnlyObject()`, `assertContainsOnlyResource()`, `assertContainsOnlyClosedResource()`, `assertContainsOnlyScalar()`, or `assertContainsOnlyString()` instead                                     |
| [#6055](https://github.com/sebastianbergmann/phpunit/issues/6055) | `Assert::assertNotContainsOnly()` | 11.5.0  | Use `assertContainsNotOnlyArray()`, `assertContainsNotOnlyBool()`, `assertContainsNotOnlyCallable()`, `assertContainsNotOnlyFloat()`, `assertContainsNotOnlyInt()`, `assertContainsNotOnlyIterable()`, `assertContainsNotOnlyNumeric()`, `assertContainsNotOnlyObject()`, `assertContainsNotOnlyResource()`, `assertContainsNotOnlyClosedResource()`, `assertContainsNotOnlyScalar()`, or `assertContainsNotOnlyString()` instead |
| [#6059](https://github.com/sebastianbergmann/phpunit/issues/6059) | `Assert::containsOnly()`          | 11.5.0  | Use `containsOnlyArray()`, `containsOnlyBool()`, `containsOnlyCallable()`, `containsOnlyFloat()`, `containsOnlyInt()`, `containsOnlyIterable()`, `containsOnlyNumeric()`, `containsOnlyObject()`, `containsOnlyResource()`, `containsOnlyClosedResource()`, `containsOnlyScalar()`, or `containsOnlyString()`  instead                                                                                                            |
| [#6510](https://github.com/sebastianbergmann/phpunit/issues/6510) | Using `with*()` on test stubs     | 12.5.11 | Using `with*()` on a test stub has no effect, use a mock object instead                                                                                                                                                                                                                                                                                                                                                           |

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
