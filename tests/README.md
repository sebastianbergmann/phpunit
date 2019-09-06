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

## Running the PHPUnit self-tests in PhpStorm

First, please configure PhpStorm in the settings:

Languages & Frameworks > PHP > Test Frameworks:
- PHPUnit library:
  - [x] Path to phpunit.phar: `phpunit` (relative to your local copy of PHPUnit)
- Test Runner:
  - [x] Default configuration file: `phpunit.xml` (relative to your local copy of PHPUnit)

Note: These configuration steps are current as of PhpStorm 2019.2.
In later versions, some settings might have been renamed or
been moved around.

Now you can execute all tests or only some tests as needed.
Please note that there are some subfolders within the `tests/`
folder that must not be included in the test scope. This breaks
running tests by folders for the end-to-end tests and the complete
test suite.

- To run all tests, please select "Defined in the configuration file"
  as test scope.
- To run the unit tests, please select the `tests/unit/` folder
  as test scope.
- You can also select individual files or folders from the unit or
  end-to-end tests as test scope.

## Structure of the self-test collection

Note: this section will change often while `tests/` is being refactored.

- `configuration.xml`: the global configuration file which defines the internal `unit` and `end-to-end` tests suites
- `tests/`
  - `_files`: specialized helper files; input/output samples
  - `basic/`: fast tests covering all basics
    - `configuration.basic.xml`: configuration file tailored for the `basic` suite
  - `end-to-end/`: run PHPUnit as a separate process and observe its behaviour via console messages and the filesystem
  - `unit/`: unit tests for individual smallest components and integration tests of common use cases
