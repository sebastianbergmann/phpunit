--TEST--
A generator that ends the PHP process with exit() while the arguments of an example are generated is reported for the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/GeneratorThatEndsTheProcessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234
%A
Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\PropertyBasedTesting\GeneratorThatEndsTheProcessTest::testExitsWhileArgumentsAreGenerated.
