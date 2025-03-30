--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5451
--SKIPIF--
<?php if(str_contains((string)ini_get('xdebug.mode'), 'develop')) {
print 'skip: xdebug.mode=develop is enabled';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5451';

require_once __DIR__ . '/../../bootstrap.php';

var_dump((new PHPUnit\TextUI\Application)->run($_SERVER['argv']));
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Issue5451\Issue5451Test::testWithErrorInDataProvider
The data provider specified for PHPUnit\TestFixture\Issue5451\Issue5451Test::testWithErrorInDataProvider is invalid
Call to a member function bar() on array

%s%eIssue5451Test.php:26

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
int(2)
