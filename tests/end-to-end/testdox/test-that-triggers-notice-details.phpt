--TEST--
TestDox: Test triggers notice and --display-notices is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--display-notices';
$_SERVER['argv'][] = __DIR__ . '/_files/NoticeTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Notice (PHPUnit\TestFixture\TestDox\Notice)
 ⚠ Notice

1 test triggered 1 notice:

1) %sNoticeTest.php:20
notice

OK, but there were issues!
Tests: 1, Assertions: 1, Notices: 1.
