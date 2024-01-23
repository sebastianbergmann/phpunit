--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5595
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.3.0-dev', PHP_VERSION, '>')) {
    print 'skip: PHP 8.3 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][]  = '--process-isolation';
$_SERVER['argv'][]  = __DIR__ . '/5595/Issue5595Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Issue5595Test::test
PHPUnit\Framework\Exception: PHP Fatal error:  Uncaught PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException: Cannot find TestCase object on call stack in %s%esrc%eEvent%eValue%eTest%eTestMethodBuilder.php:%d
Stack trace:
#0 %s%esrc%eRunner%eErrorHandler.php(%d): PHPUnit\Event\Code\TestMethodBuilder::fromCallStack()
#1 [internal function]: PHPUnit\Runner\ErrorHandler->__invoke(2, 'rewind(): Strea...', 'Standard input ...', %d)
#2 Standard input code(%d): rewind(Resource id #2)
#3 Standard input code(%d): __phpunit_run_isolated_test()
#4 {main}
  thrown in %s%esrc%eEvent%eValue%eTest%eTestMethodBuilder.php on line %d

Fatal error: Uncaught PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException: Cannot find TestCase object on call stack in %s%esrc%eEvent%eValue%eTest%eTestMethodBuilder.php on line %d

PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException: Cannot find TestCase object on call stack in %s%esrc%eEvent%eValue%eTest%eTestMethodBuilder.php on line %d

Call Stack:
    %s
    %s
    %s
    %s
    %s

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
