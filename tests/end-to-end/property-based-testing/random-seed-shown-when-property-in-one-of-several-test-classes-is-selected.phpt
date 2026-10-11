--TEST--
The random seed is shown when the test selection selects a property in one of several test classes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testReversingTwiceIsIdentity';
$_SERVER['argv'][] = __DIR__ . '/_files/PropertyAndTestTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/PassingPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   %d

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 10 assertions)
