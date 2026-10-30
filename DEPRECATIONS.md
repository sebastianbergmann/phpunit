# Deprecations

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                               | Since  | Replacement                                                                                                          |
|-------------------------------------------------------------------|-----------------------------------------------------------|--------|----------------------------------------------------------------------------------------------------------------------|
| [#6537](https://github.com/sebastianbergmann/phpunit/issues/6537) | `id()` and `after()` for mock object expectations         | 13.1.0 |                                                                                                                      |
| [#6560](https://github.com/sebastianbergmann/phpunit/issues/6560) | `expectExceptionMessage()`                                | 13.2.0 | `expectExceptionMessageIsOrContains()`                                                                               |

### Running Tests

| Issue | Description                                       | Since  | Replacement                                                |
|-------|---------------------------------------------------|--------|------------------------------------------------------------|
|       | `extensionsDirectory` XML configuration attribute | 13.5.0 | Use Composer or load the extension in the bootstrap script |

### Extending PHPUnit

| Issue | Description                                                       | Since  | Replacement |
|-------|-------------------------------------------------------------------|--------|-------------|
|       | `TextUI\Configuration\Configuration::hasPharExtensionDirectory()` | 13.5.0 |             |
|       | `TextUI\Configuration\Configuration::pharExtensionDirectory()`    | 13.5.0 |             |
|       | `Event\TestRunner\ExtensionLoadedFromPhar`                        | 13.5.0 |             |
|       | `Event\TestRunner\ExtensionLoadedFromPharSubscriber`              | 13.5.0 |             |
