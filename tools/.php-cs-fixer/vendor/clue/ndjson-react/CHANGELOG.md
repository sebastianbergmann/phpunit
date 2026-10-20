# Changelog

## 1.3.0 (2022-12-23)

*   Feature: Add support for PHP 8.1 and PHP 8.2.
    (#31 by @clue and #30 by @SimonFring)

*   Feature: Check type of incoming `data` before trying to decode NDJSON.
    (#29 by @SimonFrings)

*   Improve documentation and examples and update to new [default loop](https://reactphp.org/event-loop/#loop).
    (#26 by @clue, #27 by @SimonFrings and #25 by @PaulRotmann)

*   Improve test suite, report failed assertions and ensure 100% code coverage.
    (#32 and #33 by @clue and #28 by @SimonFrings)

## 1.2.0 (2020-12-09)

*   Improve test suite and add `.gitattributes` to exclude dev files from exports.
    Add PHP 8 support, update to PHPUnit 9 and simplify test setup.
    (#18 by @clue and #19, #22 and #23 by @SimonFrings)

## 1.1.0 (2020-02-04)

*   Feature: Improve error reporting and add parsing error message to Exception and
    ignore `JSON_THROW_ON_ERROR` option (available as of PHP 7.3).
    (#14 by @clue)

*   Feature: Add bechmarking script and import all global function references.
    (#16 by @clue)

*   Improve documentation and add NDJSON format description and
    add support / sponsorship info.
    (#12 and #17 by @clue)

*   Improve test suite to run tests on PHP 7.4 and simplify test matrix and
    apply minor code style adjustments to make phpstan happy.
    (#13 and #15 by @clue)

## 1.0.0 (2018-05-17)

*   First stable release, now following SemVer

*   Improve documentation and usage examples

> Contains no other changes, so it's actually fully compatible with the v0.1.2 release.

## 0.1.2 (2018-05-11)

*   Feature: Limit buffer size to 64 KiB by default.
    (#10 by @clue)

*   Feature: Forward compatiblity with EventLoop v0.5 and upcoming v1.0.
    (#8 by @clue)

*   Fix: Return bool `false` if encoding fails due to invalid value to pause source.
    (#9 by @clue)

*   Improve test suite by supporting PHPUnit v6 and test against legacy PHP 5.3 through PHP 7.2.
    (#7 by @clue)

*   Update project homepage.
    (#11 by @clue)

## 0.1.1 (2017-05-22)

*   Feature: Forward compatibility with Stream v0.7, v0.6, v0.5 and upcoming v1.0 (while keeping BC)
    (#6 by @thklein)

*   Improved test suite by adding PHPUnit to `require-dev`
    (#5 by @thklein)

## 0.1.0 (2016-11-24)

*   First tagged release
