--TEST--
phpunit ../../../_files/abstract/without-test-suffix/AbstractTestCase.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/abstract/without-test-suffix/AbstractTestCase.php';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
Class PHPUnit\TestFixture\AbstractTestCase declared in %sAbstractTestCase.php is abstract
