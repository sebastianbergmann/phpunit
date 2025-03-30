--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5949
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/5949/';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

........                                                            8 / 8 (100%)

Time: %s, Memory: %s

Issue5949 (PHPUnit\TestFixture\Issue5949\Issue5949)
 ✔ Test 1. No dollar sign.

 ✔ Test 2. No dollar sign.

 ✔ Test 3. Dollar sign ($).

 ✔ Test 4. No dollar sign.

 ✔ Test 5. Dollar $ sign.
           More text.

 ✔ Test 6. No dollar sign.

 ✔ Test 7. No dollar sign.

 ✔ Test 8. No dollar sign.


OK (8 tests, 8 assertions)
