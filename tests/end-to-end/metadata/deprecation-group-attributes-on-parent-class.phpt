--TEST--
Class-level #[Group], #[Ticket], #[Small], #[Medium], and #[Large] attributes on parent classes of test classes are deprecated
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = __DIR__ . '/../_files/group-attributes-on-parent-class';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

There were 4 PHPUnit test runner deprecations:

1) Class-level #[Group] attribute on PHPUnit\TestFixture\GroupAttributesOnParentClass\AbstractParentTestCase is ignored, but will be inherited by its subclasses in PHPUnit 14

2) Class-level #[Ticket] attribute on PHPUnit\TestFixture\GroupAttributesOnParentClass\AbstractParentTestCase is ignored, but will be inherited by its subclasses in PHPUnit 14

3) Class-level #[Small] attribute on PHPUnit\TestFixture\GroupAttributesOnParentClass\AbstractParentTestCase is ignored, but will be inherited by its subclasses in PHPUnit 14

4) Class-level #[Medium] attribute on PHPUnit\TestFixture\GroupAttributesOnParentClass\AbstractGrandparentTestCase is ignored, but will be inherited by its subclasses in PHPUnit 14

OK, but there were issues!
Tests: 5, Assertions: 5, PHPUnit Deprecations: 4.
