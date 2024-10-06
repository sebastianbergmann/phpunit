# PHPUnit's Own Test Suite

## Test Suite Structure

This is the top-level directory structure of the `tests` directory:

* `tests/unit` holds tests that are "regular" PHPUnit tests (implemented using `PHPUnit\Framework\TestCase`)
* `tests/end-to-end` holds tests in the [PHPT](https://qa.php.net/phpt_details.php) format
* `tests/end-to-end/phar` holds PHAR-specific tests that are not part of the regular `end-to-end` tests
* `tests/_files` holds test fixture that is used by tests in `tests/unit` and/or `tests/end-to-end`

## Running the Test Suite

* `./phpunit` will run all tests from `tests/unit` and `tests/end-to-end` (except the PHAR-specific tests)
* `./phpunit --testsuite unit` will run all tests from `tests/unit`
* `./phpunit --testsuite end-to-end` will run all tests from `tests/end-to-end` (except the PHAR-specific tests)
* `ant phar-snapshot run-phar-specific-tests` will build a PHAR and run the PHAR-specific tests
