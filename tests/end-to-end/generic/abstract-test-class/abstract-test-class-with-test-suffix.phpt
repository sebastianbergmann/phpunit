--TEST--
phpunit ../../../_files/abstract/with-test-suffix/AbstractTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/abstract/with-test-suffix/AbstractTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Class PHPUnit\TestFixture\AbstractTest declared in %sAbstractTest.php is abstract
