--TEST--
phpunit ../../../_files/abstract/without-test-suffix/ConcreteTestClassExtendingAbstractTestClassWithoutTestSuffixTest.php
--SKIPIF--
<?php declare(strict_types=1);
print 'skip: https://github.com/sebastianbergmann/phpunit/issues/4979';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/abstract/without-test-suffix/ConcreteTestClassExtendingAbstractTestClassWithoutTestSuffixTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
