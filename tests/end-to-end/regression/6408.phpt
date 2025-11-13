--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6408
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6408/Issue6408Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Issue6408\Issue6408Test::testFoo
The data provider specified for PHPUnit\TestFixture\Issue6408\Issue6408Test::testFoo is invalid
a users exception from data-provider context

%sIssue6408Test.php:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\Issue6408\Issue6408Test".

No tests executed!
