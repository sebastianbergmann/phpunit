--TEST--
#[CoversNothing] on a test method suppresses coverage for that method without warning when #[CoversClass] is used on the test class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/code-coverage-targeting/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/../_files/code-coverage-targeting/tests/CoversClassMethodCoversNothingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
