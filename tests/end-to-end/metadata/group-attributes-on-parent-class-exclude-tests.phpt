--TEST--
Tests are excluded by --exclude-group for a group that is declared on a parent class of their test class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--exclude-group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/../_files/group-attributes-on-parent-class';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test:
 - PHPUnit\TestFixture\GroupAttributesOnParentClass\ThirdTest::testOne
