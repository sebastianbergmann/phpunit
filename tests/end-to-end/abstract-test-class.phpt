--TEST--
phpunit ../../_files/AbstractTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/AbstractTest.php';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Class 'PHPUnit\TestFixture\AbstractTest' could not be found in '%sAbstractTest.php'.
