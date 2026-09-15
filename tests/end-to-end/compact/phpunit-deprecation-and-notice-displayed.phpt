--TEST--
phpunit --compact --display-phpunit-deprecations --display-phpunit-notices ../_files/PhpunitDeprecationAndNoticeTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = '--display-phpunit-notices';
$_SERVER['argv'][] = __DIR__ . '/../_files/PhpunitDeprecationAndNoticeTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

OK (3 tests, 3 assertions, 1 PHPUnit deprecation, 1 PHPUnit notice)

--- PHPUNIT DEPRECATION: PHPUnit\TestFixture\PhpunitDeprecationAndNoticeTest::testTriggersPhpunitDeprecation
deprecation message

--- PHPUNIT NOTICE: PHPUnit\TestFixture\PhpunitDeprecationAndNoticeTest::testTriggersPhpunitNotice
notice message
