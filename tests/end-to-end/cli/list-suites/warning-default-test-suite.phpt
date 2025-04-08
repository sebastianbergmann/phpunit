--TEST--
phpunit --configuration ../_files/multiple-testsuites/default-test-suite.xml --list-suites
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/multiple-testsuites/default-test-suite.xml';
$_SERVER['argv'][] = '--list-suites';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

The defaultTestSuite (XML) and --list-suites (CLI) options cannot be combined, only the default test suite is shown

Available test suite:
 - unit (1 test)
