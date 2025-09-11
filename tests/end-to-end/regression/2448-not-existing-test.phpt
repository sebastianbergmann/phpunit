--TEST--
#2448: Weird error when trying to run `Test` from `SomeNonExistingTest.php` but `SomeNonExistingTest.php` does not exist
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = 'SomeNonExistingTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test file "SomeNonExistingTest.php" not found
--CLEAN--
<?php declare(strict_types=1);
unlink(__DIR__ . '/2448/.phpunit.result.cache');
