--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6476
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--enforce-time-limit';
$_SERVER['argv'][] = '--default-time-limit=4';
$_SERVER['argv'][] = __DIR__ . '/6476/Issue6476Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\Issue6408\Issue6476Test::testIteratorAggregate
This test was aborted after 4 seconds

%sIssue6476Test.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
