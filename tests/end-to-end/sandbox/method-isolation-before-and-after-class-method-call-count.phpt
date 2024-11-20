--TEST--
Before and after class methods must not be called from primary process when test class or method is run in separated process.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/MethodIsolationBeforeAndAfterClassMethodCallCountTest.php';

require_once __DIR__ . '/../../bootstrap.php';

\file_put_contents(__DIR__ . '/_files/temp/method_before_method_call_count.txt', 0);
\file_put_contents(__DIR__ . '/_files/temp/method_after_method_call_count.txt', 0);

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

if (\intval(\file_get_contents(__DIR__ . '/_files/temp/method_before_method_call_count.txt')) !== 3){
	throw new \Exception('Invalid before class method call count!');
}

if (\intval(\file_get_contents(__DIR__ . '/_files/temp/method_after_method_call_count.txt')) !== 3){
	throw new \Exception('Invalid after class method call count!');
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 8 assertions)
