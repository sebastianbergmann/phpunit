--TEST--
phpunit ../_files/ClassLevelHookMethodsOfTestClassWhoseTestsAllRunInSeparateProcessesTest.php
--FILE--
<?php declare(strict_types=1);
$log = tempnam(sys_get_temp_dir(), __FILE__);

putenv('PHPUNIT_HOOK_METHOD_LOG=' . $log);
putenv('PHPUNIT_TEST_RUNNER_PROCESS_ID=' . getmypid());

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = __DIR__ . '/../_files/ClassLevelHookMethodsOfTestClassWhoseTestsAllRunInSeparateProcessesTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($log);

unlink($log);
--EXPECT--
setUpBeforeClass() called in child process
tearDownAfterClass() called in child process
setUpBeforeClass() called in child process
tearDownAfterClass() called in child process
