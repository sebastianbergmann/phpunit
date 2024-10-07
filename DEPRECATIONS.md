# Deprecations

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Running Tests

| Issue                                                             | Description                                                                                                                           | Since  | Replacement                                                                                        |
|-------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------|--------|----------------------------------------------------------------------------------------------------|
| [#5689](https://github.com/sebastianbergmann/phpunit/issues/5689) | `restrictDeprecations` attribute on the `<source>` element of the XML configuration file                                              | 11.1.0 | Use `ignoreSelfDeprecations`, `ignoreDirectDeprecations`, and `ignoreIndirectDeprecations` instead |

#### Miscellaneous

| Issue                                                             | Description                                                 | Since  | Replacement                                                                             |
|-------------------------------------------------------------------|-------------------------------------------------------------|--------|-----------------------------------------------------------------------------------------|
| [#4505](https://github.com/sebastianbergmann/phpunit/issues/4505) | Metadata in doc-comments                                    | 10.3.0 | Metadata in attributes                                                                  |
