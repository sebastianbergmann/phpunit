--TEST--
Only the tests that are affected by what changed cannot be run when no cache directory is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-without-cache-directory.xml';
$_SERVER['argv'][] = '--only-impacted';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot run only the tests that are affected by what changed because no cache directory is configured
