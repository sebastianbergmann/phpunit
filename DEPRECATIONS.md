# Deprecations

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Test Double API

| Issue                                                             | Description                                                                    | Since  | Replacement                                                                             |
|-------------------------------------------------------------------|--------------------------------------------------------------------------------|--------|-----------------------------------------------------------------------------------------|
| [#5415](https://github.com/sebastianbergmann/phpunit/issues/5415) | Support for doubling interfaces (or classes) that have a method named `method` | 11.0.0 |                                                                                         |
| [#5535](https://github.com/sebastianbergmann/phpunit/issues/5525) | Configuring expectations using `expects()` on test stubs                       | 11.0.0 | Create a mock object when you need to configure expectations on a test double           |

### Running Tests

| Issue                                                             | Description                                                                                                                           | Since  | Replacement                                                                                        |
|-------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------|--------|----------------------------------------------------------------------------------------------------|
| [#5689](https://github.com/sebastianbergmann/phpunit/issues/5689) | `restrictDeprecations` attribute on the `<source>` element of the XML configuration file                                              | 11.1.0 | Use `ignoreSelfDeprecations`, `ignoreDirectDeprecations`, and `ignoreIndirectDeprecations` instead |

#### Miscellaneous

| Issue                                                             | Description                                                 | Since  | Replacement                                                                             |
|-------------------------------------------------------------------|-------------------------------------------------------------|--------|-----------------------------------------------------------------------------------------|
| [#4505](https://github.com/sebastianbergmann/phpunit/issues/4505) | Metadata in doc-comments                                    | 10.3.0 | Metadata in attributes                                                                  |
