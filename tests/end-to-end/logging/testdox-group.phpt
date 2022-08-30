--TEST--
phpunit --testdox-text php://stdout --testdox-group one ../../_files/TestDoxGroupTest.php
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-text';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = '--testdox-group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/TestDoxGroupTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main(false);

print file_get_contents($output);

unlink($output);
--EXPECTF--
Dox Group (PHPUnit\TestFixture\DoxGroup)
 [x] One
