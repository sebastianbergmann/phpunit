--TEST--
phpunit --parallel=2 runs tests attributed with #[DoNotRunInParallel] sequentially and forwards all results in suite order
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

Class Level (PHPUnit\TestFixture\DoNotRunInParallel\ClassLevel)
 ✔ Class level

Inherited (PHPUnit\TestFixture\DoNotRunInParallel\Inherited)
 ✔ Inherited

Method Level (PHPUnit\TestFixture\DoNotRunInParallel\MethodLevel)
 ✔ Plain method
 ✔ Sequential method

Plain (PHPUnit\TestFixture\DoNotRunInParallel\Plain)
 ✔ Plain

OK (5 tests, 5 assertions)
