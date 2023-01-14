--TEST--
phpunit ../../../_files/abstract/with-test-suffix
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/abstract/with-test-suffix';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Warning:       Abstract test case classes with "Test" suffix are deprecated (PHPUnit\TestFixture\AbstractTest)

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
