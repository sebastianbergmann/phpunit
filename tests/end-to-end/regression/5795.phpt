--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5795
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/5795/Issue5795Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

Issue5795 (PHPUnit\TestFixture\Issue5795\Issue5795)
 ✔ This test should make phpunit spit a PHP Warning ! with data set #0
 ✔ This test should make phpunit spit a PHP Warning ! with data set #1
 ✔ This test should make phpunit spit a PHP Warning ! with data set #2

OK (3 tests, 3 assertions)
