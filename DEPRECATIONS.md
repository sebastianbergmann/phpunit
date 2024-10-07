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
| [#5709](https://github.com/sebastianbergmann/phpunit/issues/5709) | Support for using comma-separated values with the `--group`, `--exclude-group`, `--covers`, `--uses`, and `--test-suffix` CLI options | 11.1.0 | Use `--group foo --group bar` instead of `--group foo,bar`, for example                            |

#### Miscellaneous

| Issue                                                             | Description                                                 | Since  | Replacement                                                                             |
|-------------------------------------------------------------------|-------------------------------------------------------------|--------|-----------------------------------------------------------------------------------------|
| [#4505](https://github.com/sebastianbergmann/phpunit/issues/4505) | Metadata in doc-comments                                    | 10.3.0 | Metadata in attributes                                                                  |
| [#5800](https://github.com/sebastianbergmann/phpunit/issues/5800) | Targeting traits with `#[CoversClass]` and `#[UsesClass]`   | 11.2.0 | `#[CoversClass]` and `#[UsesClass]` also target the traits used by the targeted classes |
| [#5951](https://github.com/sebastianbergmann/phpunit/issues/5951) | `includeUncoveredFiles` configuration option                | 11.4.0 |                                                                                         |
| [#5958](https://github.com/sebastianbergmann/phpunit/issues/5958) | `#[CoversTrait]` and `#[UsesTrait]` attributes              | 11.4.0 | `#[CoversClass]` and `#[UsesClass]` also target the traits used by the targeted classes |
| [#5960](https://github.com/sebastianbergmann/phpunit/issues/5960) | Targeting traits with `#[CoversMethod]` and `#[UsesMethod]` | 11.4.0 |                                                                                         |
