# PHPUnit self-tests

This document contains the notes of projects contributors about testing the PHPUnit core and its integration with the most important dependencies. 

## Quick start

There are two main ways to self-test PHPUnit. The first is to test _everything_ using the default configuration. Here's how to do that with pretty colors in a human-readable format: 

```
cd /path/to/phpunit
./phpunit --testdox --colors=always --verbose
```

If you want to do a very quick check health-check of most basic use cases you can use the `basic` test collection:

```
./phpunit --testdox --colors=always --verbose -c tests/basic/configuration.basic.xml
```

The `basic` suite of tests puts the core system through its paces and covers most of the basic use cases of PHPUnit including happy flows and common exceptions. 

## Structure of the self-test collection

Note: this section will change often while `tests/` is being refactored.

- `configuration.xml`: the global configuration file which defines the internal `unit` and `end-to-end` tests suites
- `tests/`
  - `_files`: specialized helper files; input/output samples
  - `basic/`: fast tests covering all basics
    - `configuration.basic.xml`: configuration file tailored for the `basic` suite
  - `end-to-end/`: run PHPUnit as a separate process and observe its behaviour via console messages and the filesystem
  - `unit/`: unit tests for individual smallest components and integration tests of common use cases
