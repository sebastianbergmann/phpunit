--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6173
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6173/Issue6173Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

hello, success!
.hello, fail!
F                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue6173\Issue6173Test::test_log_fail
Failed asserting that false is true.

%sIssue6173Test.php:%d

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
