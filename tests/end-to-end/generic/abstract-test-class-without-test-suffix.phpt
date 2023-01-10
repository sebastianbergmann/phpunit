--TEST--
phpunit ../../_files/AbstractTestCase.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/AbstractTestCase.php';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Class 'PHPUnit\TestFixture\AbstractTestCase' could not be found in '%sAbstractTestCase.php'.
