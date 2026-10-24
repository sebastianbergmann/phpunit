--TEST--
Class-level #[Group], #[Ticket], #[Small], #[Medium], and #[Large] attributes on parent classes are inherited by the tests of a test class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-groups';
$_SERVER['argv'][] = __DIR__ . '/../_files/group-attributes-on-parent-class';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test groups:
 - 1234 (3 tests)
 - large (1 test)
 - medium (1 test)
 - one (3 tests)
 - small (2 tests)
 - two (3 tests)
