--TEST--
Shutdown Handler: exit(1)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testWithMessage';
$_SERVER['argv'][] = __DIR__ . '/../_files/WithExitTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

messageFatal error: Premature end of PHP process when running PHPUnit\TestFixture\WithExitTest::testWithMessage.
