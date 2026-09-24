--TEST--
Class-level #[Group], #[Ticket], #[Small], #[Medium], and #[Large] attributes on parent classes are not deprecated when the test class declares the same groups and a size
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = __DIR__ . '/../_files/group-attributes-on-parent-class/FirstTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
