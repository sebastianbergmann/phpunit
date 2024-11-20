--TEST--
Before and after class methods must not be called from primary process when test class or method is run in separated process.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/ClassIsolationBeforeAndAfterClassMethodCallCountTest.php';

require_once __DIR__ . '/../../bootstrap.php';

\file_put_contents(__DIR__ . '/_files/temp/class_before_method_call_count.txt', 0);
\file_put_contents(__DIR__ . '/_files/temp/class_after_method_call_count.txt', 0);

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

if (\intval(\file_get_contents(__DIR__ . '/_files/temp/class_after_method_call_count.txt')) !== 1){
	throw new \Exception('Invalid after class method call count!');
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 4 assertions)
