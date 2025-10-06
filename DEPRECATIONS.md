# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

### Attributes

| Issue                                                             | Description                                                                                 | Since  | Replacement                                  |
|-------------------------------------------------------------------|---------------------------------------------------------------------------------------------|--------|----------------------------------------------|
| [#6284](https://github.com/sebastianbergmann/phpunit/issues/6284) | Using `#[RunClassInSeparateProcess]` on a test class                                        | 12.4.0 | Use `#[RunTestsInSeparateProcesses]` instead |
| [#6355](https://github.com/sebastianbergmann/phpunit/issues/6355) | Support for version constraint string argument without explicit version comparison operator | 12.4.0 |                                              |
