--TEST--
A test errors when a class-level #[Group] attribute on a parent class of its test class cannot be instantiated
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/invalid-group-attribute-on-parent-class/InvalidGroupAttributeOnParentTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\InvalidGroupAttributeOnParentClass\InvalidGroupAttributeOnParentTest::testOne
Invalid attribute PHPUnit\Framework\Attributes\Group for class PHPUnit\TestFixture\InvalidGroupAttributeOnParentClass\AbstractParentWithInvalidGroupAttributeTestCase in %sInvalidGroupAttributeOnParentTest.php:16
Too few arguments to function PHPUnit\Framework\Attributes\Group::__construct(), 0 passed in %sInvalidGroupAttributeOnParentTest.php on line 15 and exactly 1 expected

%sInvalidGroupAttributeOnParentTest.php:22

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\InvalidGroupAttributeOnParentClass\InvalidGroupAttributeOnParentTest".

No tests executed!
