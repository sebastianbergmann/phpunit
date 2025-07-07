--TEST--
The right events are emitted in the right order for a test that runs code which triggers a PHPUnit warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/PhpunitWarningIgnoredTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

...W.                                                               5 / 5 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\Event\PhpunitWarningIgnoredTest::testPhpunitWarningWithWrongPattern
another message

%sPhpunitWarningIgnoredTest.php:%d

OK, but there were issues!
Tests: 5, Assertions: 5, PHPUnit Warnings: 1.
