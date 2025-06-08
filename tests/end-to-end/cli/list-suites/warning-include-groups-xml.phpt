--TEST--
phpunit --configuration ../_files/multiple-testsuites/include-group.xml --list-suites
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/multiple-testsuites/include-group.xml';
$_SERVER['argv'][] = '--list-suites';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

The --group (CLI) and <groups> (XML) options cannot be combined with --list-suites, --group and <groups> are ignored

Available test suites:
 - end-to-end (1 test)
 - unit (1 test)
