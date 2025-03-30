--TEST--
GH-765: Fatal error triggered in PHPUnit when exception is thrown in data provider of a test with a dependency
--SKIPIF--
<?php if(str_contains((string)ini_get('xdebug.mode'), 'develop')) {
print 'skip: xdebug.mode=develop is enabled';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/765/Issue765Test.php';

require_once __DIR__ . '/../../bootstrap.php';

var_dump((new PHPUnit\TextUI\Application)->run($_SERVER['argv']));
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Issue765Test::testDependent
The data provider specified for PHPUnit\TestFixture\Issue765Test::testDependent is invalid
<no message>

%s:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
int(2)
