--TEST--
Which tests are recorded as having executed a source file cannot be asked when no cache directory is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-without-cache-directory.xml';
$_SERVER['argv'][] = '--list-tests-that-depend-on';
$_SERVER['argv'][] = __DIR__ . '/_files/src/Rounder.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot list tests that executed a source file because no cache directory is configured
