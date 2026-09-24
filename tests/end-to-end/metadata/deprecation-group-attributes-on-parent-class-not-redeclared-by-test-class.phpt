--TEST--
Class-level #[Group], #[Ticket], #[Small], #[Medium], and #[Large] attributes on parent classes are deprecated when the test class does not declare the same groups and a size
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = __DIR__ . '/../_files/group-attributes-on-parent-class/SecondTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 3 PHPUnit test runner deprecations:

1) Class-level #[Group] attribute on PHPUnit\TestFixture\GroupAttributesOnParentClass\AbstractParentTestCase is ignored, but will be inherited by its subclasses in PHPUnit 14

2) Class-level #[Ticket] attribute on PHPUnit\TestFixture\GroupAttributesOnParentClass\AbstractParentTestCase is ignored, but will be inherited by its subclasses in PHPUnit 14

3) Class-level #[Small] attribute on PHPUnit\TestFixture\GroupAttributesOnParentClass\AbstractParentTestCase is ignored, but will be inherited by its subclasses in PHPUnit 14

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Deprecations: 3.
