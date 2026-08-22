--TEST--
--list-suites does not use the test index, and does not ask for somewhere to keep it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/multiple-testsuites/phpunit.xml';
$_SERVER['argv'][] = '--cache-test-index';
$_SERVER['argv'][] = '--list-suites';

require_once __DIR__ . '/../../../bootstrap.php';

/*
 * That there is no warning about the missing cache directory is what shows
 * that --list-suites turns the index off before anything is done for it: the
 * configuration file this uses configures no cache directory.
 */
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test suites:
 - end-to-end (1 test)
 - unit (1 test)
