--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6234
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6234/Issue6234Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Fatal error: Premature end of PHP process in PHPUnit\TestFixture\Issue6234Test::testExitWithoutProcessIsolation.
