--TEST--
#[TestDoxFormatter]: Formatter method throws exception
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/FormatterMethodThrowsExceptionTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Formatter Method Throws Exception (PHPUnit\TestFixture\TestDox\FormatterMethodThrowsException)
 ✔ One with data set #0

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\TestDox\FormatterMethodThrowsExceptionTest::testOne with data set #0 ('string')
TestDox formatter PHPUnit\TestFixture\TestDox\FormatterMethodThrowsExceptionTest::formatter() triggered an error: message
%sFormatterMethodThrowsExceptionTest.php:%d

%sFormatterMethodThrowsExceptionTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
