--TEST--
#[TestDoxFormatter]: Formatter method is not public
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/FormatterMethodIsNotPublicTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Formatter Method Is Not Public (PHPUnit\TestFixture\TestDox\FormatterMethodIsNotPublic)
 ✔ One with data set #0

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\TestDox\FormatterMethodIsNotPublicTest::testOne with data set #0 ('string')
Method PHPUnit\TestFixture\TestDox\FormatterMethodIsNotPublicTest::formatter() cannot be used as a TestDox formatter because it is not public

%sFormatterMethodIsNotPublicTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
