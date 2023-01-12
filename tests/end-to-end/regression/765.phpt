--TEST--
GH-765: Fatal error triggered in PHPUnit when exception is thrown in data provider of a test with a dependency
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/765/Issue765Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
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

OK (1 test, 1 assertion)
