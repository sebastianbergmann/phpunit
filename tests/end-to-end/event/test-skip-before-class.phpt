--TEST--
Tests skipped before class should not be counted as executed tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/skip-in-before-class';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

...............................................................  63 / 102 ( 61%)
.....................................SS                         102 / 102 (100%)

Time: %s, Memory: %s MB

OK, but some tests were skipped!
Tests: 102, Assertions: 100, Skipped: 2.

