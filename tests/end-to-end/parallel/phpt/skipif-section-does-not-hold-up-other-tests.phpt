--TEST--
phpunit --parallel=2 goes on running the other PHPT tests while the --SKIPIF-- section of a PHPT test runs in a child process
--FILE--
<?php declare(strict_types=1);
$handshake = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-parallel-skipif-' . getmypid();

putenv('PHPUNIT_TEST_HANDSHAKE=' . $handshake);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/skipif-in-a-child-process/first-waits-in-its-skipif-section.phpt';
$_SERVER['argv'][] = __DIR__ . '/_files/skipif-in-a-child-process/second-creates-the-file.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

@unlink($handshake . '.created');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
