# Changes in PHPUnit 6.1

All notable changes of the PHPUnit 6.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.2.0] - 2017-06-02

### Changed

* When `beStrictAboutCoversAnnotation="true"` is configured or `--strict-coverage` is used then a test is now also marked as risky when it specifies units of code using `@covers` or `@uses` that are not executed by the test

[6.2.0]: https://github.com/sebastianbergmann/phpunit/compare/6.1...6.2.0

