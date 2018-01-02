<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\Constraint\IsEmpty;
use PHPUnit\Framework\Constraint\SameSize;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Util\Filter;

class ConstraintTest extends TestCase
{
    public function testConstraintArrayHasKey(): void
    {
        $constraint = Assert::arrayHasKey(0);

        $this->assertFalse($constraint->evaluate([], '', true));
        $this->assertEquals('has the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array has the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayHasKey2(): void
    {
        $constraint = Assert::arrayHasKey(0);

        try {
            $constraint->evaluate([], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that an array has the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotHasKey(): void
    {
        $constraint = Assert::logicalNot(
            Assert::arrayHasKey(0)
        );

        $this->assertFalse($constraint->evaluate([0 => 1], '', true));
        $this->assertEquals('does not have the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([0 => 1]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array does not have the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotHasKey2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::arrayHasKey(0)
        );

        try {
            $constraint->evaluate([0], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that an array does not have the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsReadable(): void
    {
        $constraint = Assert::isReadable();

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertEquals('is readable', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that "foo" is readable.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsWritable(): void
    {
        $constraint = Assert::isWritable();

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertEquals('is writable', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that "foo" is writable.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintDirectoryExists(): void
    {
        $constraint = Assert::directoryExists();

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertEquals('directory exists', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that directory "foo" exists.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintFileExists(): void
    {
        $constraint = Assert::fileExists();

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertEquals('file exists', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that file "foo" exists.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintFileExists2(): void
    {
        $constraint = Assert::fileExists();

        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that file "foo" exists.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintFileNotExists(): void
    {
        $file = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';

        $constraint = Assert::logicalNot(
            Assert::fileExists()
        );

        $this->assertFalse($constraint->evaluate($file, '', true));
        $this->assertEquals('file does not exist', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($file);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that file "$file" does not exist.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintFileNotExists2(): void
    {
        $file = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';

        $constraint = Assert::logicalNot(
            Assert::fileExists()
        );

        try {
            $constraint->evaluate($file, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that file "$file" does not exist.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintGreaterThan(): void
    {
        $constraint = Assert::greaterThan(1);

        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertTrue($constraint->evaluate(2, '', true));
        $this->assertEquals('is greater than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintGreaterThan2(): void
    {
        $constraint = Assert::greaterThan(1);

        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThan(): void
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThan(1)
        );

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertEquals('is not greater than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(2);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 2 is not greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThan2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThan(1)
        );

        try {
            $constraint->evaluate(2, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 2 is not greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintGreaterThanOrEqual(): void
    {
        $constraint = Assert::greaterThanOrEqual(1);

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertEquals('is equal to 1 or is greater than 1', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is equal to 1 or is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintGreaterThanOrEqual2(): void
    {
        $constraint = Assert::greaterThanOrEqual(1);

        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is equal to 1 or is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThanOrEqual(): void
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThanOrEqual(1)
        );

        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('not( is equal to 1 or is greater than 1 )', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that not( 1 is equal to 1 or is greater than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThanOrEqual2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThanOrEqual(1)
        );

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that not( 1 is equal to 1 or is greater than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsAnything(): void
    {
        $constraint = Assert::anything();

        $this->assertTrue($constraint->evaluate(null, '', true));
        $this->assertNull($constraint->evaluate(null));
        $this->assertEquals('is anything', $constraint->toString());
        $this->assertCount(0, $constraint);
    }

    public function testConstraintNotIsAnything(): void
    {
        $constraint = Assert::logicalNot(
            Assert::anything()
        );

        $this->assertFalse($constraint->evaluate(null, '', true));
        $this->assertEquals('is not anything', $constraint->toString());
        $this->assertCount(0, $constraint);

        try {
            $constraint->evaluate(null);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that null is not anything.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsEqual(): void
    {
        $constraint = Assert::equalTo(1);

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertEquals('is equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 matches expected 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function isEqualProvider()
    {
        $a      = new \stdClass;
        $a->foo = 'bar';
        $b      = new \stdClass;
        $ahash  = \spl_object_hash($a);
        $bhash  = \spl_object_hash($b);

        $c               = new \stdClass;
        $c->foo          = 'bar';
        $c->int          = 1;
        $c->array        = [0, [1], [2], 3];
        $c->related      = new \stdClass;
        $c->related->foo = "a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk";
        $c->self         = $c;
        $c->c            = $c;
        $d               = new \stdClass;
        $d->foo          = 'bar';
        $d->int          = 2;
        $d->array        = [0, [4], [2], 3];
        $d->related      = new \stdClass;
        $d->related->foo = "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk";
        $d->self         = $d;
        $d->c            = $c;

        $storage1 = new \SplObjectStorage;
        $storage1->attach($a);
        $storage1->attach($b);
        $storage2 = new \SplObjectStorage;
        $storage2->attach($b);
        $storage1hash = \spl_object_hash($storage1);
        $storage2hash = \spl_object_hash($storage2);

        $dom1                     = new \DOMDocument;
        $dom1->preserveWhiteSpace = false;
        $dom1->loadXML('<root></root>');
        $dom2                     = new \DOMDocument;
        $dom2->preserveWhiteSpace = false;
        $dom2->loadXML('<root><foo/></root>');

        $data = [
            [1, 0, <<<EOF
Failed asserting that 0 matches expected 1.

EOF
            ],
            [1.1, 0, <<<EOF
Failed asserting that 0 matches expected 1.1.

EOF
            ],
            ['a', 'b', <<<EOF
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'a'
+'b'

EOF
            ],
            ["a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk", "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk", <<<EOF
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
 'a\\n
-b\\n
+p\\n
@@ @@
-j\\n
+w\\n

EOF
            ],
            [1, [0], <<<EOF
Array (...) does not match expected type "integer".

EOF
            ],
            [[0], 1, <<<EOF
1 does not match expected type "array".

EOF
            ],
            [[0], [1], <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 0
+    0 => 1

EOF
            ],
            [[true], ['true'], <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => true
+    0 => 'true'

EOF
            ],
            [[0, [1], [2], 3], [0, [4], [2], 3], <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
     0 => 0
     1 => Array (
-        0 => 1
+        0 => 4

EOF
            ],
            [$a, [0], <<<EOF
Array (...) does not match expected type "object".

EOF
            ],
            [[0], $a, <<<EOF
stdClass Object (...) does not match expected type "array".

EOF
            ],
            [$a, $b, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
-    'foo' => 'bar'

EOF
            ],
            [$c, $d, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
     'foo' => 'bar'
-    'int' => 1
+    'int' => 2
     'array' => Array (
         0 => 0
         1 => Array (
-            0 => 1
+            0 => 4
@@ @@
-        b\\n
+        p\\n
@@ @@
-        j\\n
+        w\\n

EOF
            ],
            [$dom1, $dom2, <<<EOF
Failed asserting that two DOM documents are equal.
--- Expected
+++ Actual
@@ @@
 <?xml version="1.0"?>
-<root/>
+<root>
+  <foo/>
+</root>

EOF
            ],
            [
              new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
              new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/Chicago')),
              <<<EOF
Failed asserting that two DateTime objects are equal.
--- Expected
+++ Actual
@@ @@
-2013-03-29T04:13:35.000000-0400
+2013-03-29T04:13:35.000000-0500

EOF
            ],
        ];

        if (PHP_MAJOR_VERSION < 7) {
            $data[] = [$storage1, $storage2, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
-SplObjectStorage Object &$storage1hash (
-    '$ahash' => Array &0 (
-        'obj' => stdClass Object &$ahash (
-            'foo' => 'bar'
-        )
-        'inf' => null
-    )
-    '$bhash' => Array &1 (
+SplObjectStorage Object &$storage2hash (
+    '$bhash' => Array &0 (

EOF
            ];
        } else {
            $data[] = [$storage1, $storage2, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
-SplObjectStorage Object &$storage1hash (
-    '$ahash' => Array &0 (
-        'obj' => stdClass Object &$ahash (
-            'foo' => 'bar'
-        )
-        'inf' => null
-    )
-    '$bhash' => Array &1 (
+SplObjectStorage Object &$storage2hash (
+    '$bhash' => Array &0 (

EOF
            ];
        }

        return $data;
    }

    /**
     * @dataProvider isEqualProvider
     *
     * @param mixed $expected
     * @param mixed $actual
     * @param mixed $message
     *
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testConstraintIsEqual2($expected, $actual, $message): void
    {
        $constraint = Assert::equalTo($expected);

        try {
            $constraint->evaluate($actual, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                "custom message\n$message",
                $this->trimnl(TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotEqual(): void
    {
        $constraint = Assert::logicalNot(
            Assert::equalTo(1)
        );

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('is not equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 1 is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotEqual2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::equalTo(1)
        );

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 1 is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdentical(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $constraint = Assert::identicalTo($a);

        $this->assertFalse($constraint->evaluate($b, '', true));
        $this->assertTrue($constraint->evaluate($a, '', true));
        $this->assertEquals('is identical to an object of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($b);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that two variables reference the same object.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdentical2(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $constraint = Assert::identicalTo($a);

        try {
            $constraint->evaluate($b, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two variables reference the same object.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdentical3(): void
    {
        $constraint = Assert::identicalTo('a');

        try {
            $constraint->evaluate('b', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'a'
+'b'

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $constraint = Assert::logicalNot(
            Assert::identicalTo($a)
        );

        $this->assertTrue($constraint->evaluate($b, '', true));
        $this->assertFalse($constraint->evaluate($a, '', true));
        $this->assertEquals('is not identical to an object of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($a);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that two variables don't reference the same object.

EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical2(): void
    {
        $a = new \stdClass;

        $constraint = Assert::logicalNot(
            Assert::identicalTo($a)
        );

        try {
            $constraint->evaluate($a, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two variables don't reference the same object.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical3(): void
    {
        $constraint = Assert::logicalNot(
            Assert::identicalTo('a')
        );

        try {
            $constraint->evaluate('a', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two strings are not identical.

EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsInstanceOf(): void
    {
        $constraint = Assert::isInstanceOf(\Exception::class);

        $this->assertFalse($constraint->evaluate(new \stdClass, '', true));
        $this->assertTrue($constraint->evaluate(new \Exception, '', true));
        $this->assertEquals('is instance of class "Exception"', $constraint->toString());
        $this->assertCount(1, $constraint);

        $interfaceConstraint = Assert::isInstanceOf(\Countable::class);
        $this->assertFalse($interfaceConstraint->evaluate(new \stdClass, '', true));
        $this->assertTrue($interfaceConstraint->evaluate(new \ArrayObject, '', true));
        $this->assertEquals('is instance of interface "Countable"', $interfaceConstraint->toString());

        try {
            $constraint->evaluate(new \stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that stdClass Object () is an instance of class "Exception".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsInstanceOf2(): void
    {
        $constraint = Assert::isInstanceOf(\Exception::class);

        try {
            $constraint->evaluate(new \stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that stdClass Object () is an instance of class "Exception".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotInstanceOf(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isInstanceOf(\stdClass::class)
        );

        $this->assertFalse($constraint->evaluate(new \stdClass, '', true));
        $this->assertTrue($constraint->evaluate(new Exception, '', true));
        $this->assertEquals('is not instance of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new \stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that stdClass Object () is not an instance of class "stdClass".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotInstanceOf2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isInstanceOf(\stdClass::class)
        );

        try {
            $constraint->evaluate(new \stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that stdClass Object () is not an instance of class "stdClass".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsType(): void
    {
        $constraint = Assert::isType('string');

        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertTrue($constraint->evaluate('', '', true));
        $this->assertEquals('is of type "string"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new \stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
Failed asserting that stdClass Object &%x () is of type "string".

EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsType2(): void
    {
        $constraint = Assert::isType('string');

        try {
            $constraint->evaluate(new \stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
custom message
Failed asserting that stdClass Object &%x () is of type "string".

EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    public function resources()
    {
        $fh = \fopen(__FILE__, 'r');
        \fclose($fh);

        return [
            'open resource'     => [\fopen(__FILE__, 'r')],
            'closed resource'   => [$fh],
        ];
    }

    /**
     * @dataProvider resources
     *
     * @param mixed $resource
     *
     * @throws ExpectationFailedException
     * @throws \Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testConstraintIsResourceTypeEvaluatesCorrectlyWithResources($resource): void
    {
        $constraint = Assert::isType('resource');

        $this->assertTrue($constraint->evaluate($resource, '', true));

        @\fclose($resource);
    }

    public function testConstraintIsNotType(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isType('string')
        );

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertFalse($constraint->evaluate('', '', true));
        $this->assertEquals('is not of type "string"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that '' is not of type "string".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotType2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isType('string')
        );

        try {
            $constraint->evaluate('', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that '' is not of type "string".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNull(): void
    {
        $constraint = Assert::isNull();

        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertTrue($constraint->evaluate(null, '', true));
        $this->assertEquals('is null', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is null.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNull2(): void
    {
        $constraint = Assert::isNull();

        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is null.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotNull(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isNull()
        );

        $this->assertFalse($constraint->evaluate(null, '', true));
        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertEquals('is not null', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(null);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that null is not null.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotNull2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isNull()
        );

        try {
            $constraint->evaluate(null, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that null is not null.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintLessThan(): void
    {
        $constraint = Assert::lessThan(1);

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('is less than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 1 is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintLessThan2(): void
    {
        $constraint = Assert::lessThan(1);

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 1 is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThan(): void
    {
        $constraint = Assert::logicalNot(
            Assert::lessThan(1)
        );

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertEquals('is not less than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is not less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThan2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::lessThan(1)
        );

        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is not less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintLessThanOrEqual(): void
    {
        $constraint = Assert::lessThanOrEqual(1);

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertFalse($constraint->evaluate(2, '', true));
        $this->assertEquals('is equal to 1 or is less than 1', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(2);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 2 is equal to 1 or is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintCallback(): void
    {
        $closureReflect = function ($parameter) {
            return $parameter;
        };

        $closureWithoutParameter = function () {
            return true;
        };

        $constraint = Assert::callback($closureWithoutParameter);
        $this->assertTrue($constraint->evaluate('', '', true));

        $constraint = Assert::callback($closureReflect);
        $this->assertTrue($constraint->evaluate(true, '', true));
        $this->assertFalse($constraint->evaluate(false, '', true));

        $callback   = [$this, 'callbackReturningTrue'];
        $constraint = Assert::callback($callback);
        $this->assertTrue($constraint->evaluate(false, '', true));

        $callback   = [self::class, 'staticCallbackReturningTrue'];
        $constraint = Assert::callback($callback);
        $this->assertTrue($constraint->evaluate(null, '', true));

        $this->assertEquals('is accepted by specified callback', $constraint->toString());
    }

    public function testConstraintCallbackFailure(): void
    {
        $constraint = Assert::callback(function () {
            return false;
        });

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that \'This fails\' is accepted by specified callback.');

        $constraint->evaluate('This fails');
    }

    public function callbackReturningTrue()
    {
        return true;
    }

    public static function staticCallbackReturningTrue()
    {
        return true;
    }

    public function testConstraintLessThanOrEqual2(): void
    {
        $constraint = Assert::lessThanOrEqual(1);

        try {
            $constraint->evaluate(2, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 2 is equal to 1 or is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThanOrEqual(): void
    {
        $constraint = Assert::logicalNot(
            Assert::lessThanOrEqual(1)
        );

        $this->assertTrue($constraint->evaluate(2, '', true));
        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('not( is equal to 1 or is less than 1 )', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that not( 1 is equal to 1 or is less than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThanOrEqual2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::lessThanOrEqual(1)
        );

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that not( 1 is equal to 1 or is less than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassHasAttribute(): void
    {
        $constraint = Assert::classHasAttribute('privateAttribute');

        $this->assertTrue($constraint->evaluate(\ClassWithNonPublicAttributes::class, '', true));
        $this->assertFalse($constraint->evaluate(\stdClass::class, '', true));
        $this->assertEquals('has attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(\stdClass::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that class "stdClass" has attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassHasAttribute2(): void
    {
        $constraint = Assert::classHasAttribute('privateAttribute');

        try {
            $constraint->evaluate(\stdClass::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that class "stdClass" has attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasAttribute(): void
    {
        $constraint = Assert::logicalNot(
            Assert::classHasAttribute('privateAttribute')
        );

        $this->assertTrue($constraint->evaluate(\stdClass::class, '', true));
        $this->assertFalse($constraint->evaluate(\ClassWithNonPublicAttributes::class, '', true));
        $this->assertEquals('does not have attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasAttribute2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::classHasAttribute('privateAttribute')
        );

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassHasStaticAttribute(): void
    {
        $constraint = Assert::classHasStaticAttribute('privateStaticAttribute');

        $this->assertTrue($constraint->evaluate(\ClassWithNonPublicAttributes::class, '', true));
        $this->assertFalse($constraint->evaluate(\stdClass::class, '', true));
        $this->assertEquals('has static attribute "privateStaticAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(\stdClass::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that class "stdClass" has static attribute "privateStaticAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassHasStaticAttribute2(): void
    {
        $constraint = Assert::classHasStaticAttribute('foo');

        try {
            $constraint->evaluate(\stdClass::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that class "stdClass" has static attribute "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasStaticAttribute(): void
    {
        $constraint = Assert::logicalNot(
            Assert::classHasStaticAttribute('privateStaticAttribute')
        );

        $this->assertTrue($constraint->evaluate(\stdClass::class, '', true));
        $this->assertFalse($constraint->evaluate(\ClassWithNonPublicAttributes::class, '', true));
        $this->assertEquals('does not have static attribute "privateStaticAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "privateStaticAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasStaticAttribute2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::classHasStaticAttribute('privateStaticAttribute')
        );

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "privateStaticAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintObjectHasAttribute(): void
    {
        $constraint = Assert::objectHasAttribute('privateAttribute');

        $this->assertTrue($constraint->evaluate(new \ClassWithNonPublicAttributes, '', true));
        $this->assertFalse($constraint->evaluate(new \stdClass, '', true));
        $this->assertEquals('has attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new \stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that object of class "stdClass" has attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintObjectHasAttribute2(): void
    {
        $constraint = Assert::objectHasAttribute('privateAttribute');

        try {
            $constraint->evaluate(new \stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that object of class "stdClass" has attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintObjectNotHasAttribute(): void
    {
        $constraint = Assert::logicalNot(
            Assert::objectHasAttribute('privateAttribute')
        );

        $this->assertTrue($constraint->evaluate(new \stdClass, '', true));
        $this->assertFalse($constraint->evaluate(new \ClassWithNonPublicAttributes, '', true));
        $this->assertEquals('does not have attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new \ClassWithNonPublicAttributes);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that object of class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintObjectNotHasAttribute2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::objectHasAttribute('privateAttribute')
        );

        try {
            $constraint->evaluate(new \ClassWithNonPublicAttributes, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that object of class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintRegularExpression(): void
    {
        $constraint = Assert::matchesRegularExpression('/foo/');

        $this->assertFalse($constraint->evaluate('barbazbar', '', true));
        $this->assertTrue($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('matches PCRE pattern "/foo/"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barbazbar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barbazbar' matches PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintRegularExpression2(): void
    {
        $constraint = Assert::matchesRegularExpression('/foo/');

        try {
            $constraint->evaluate('barbazbar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barbazbar' matches PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintPCRENotMatch(): void
    {
        $constraint = Assert::logicalNot(
            Assert::matchesRegularExpression('/foo/')
        );

        $this->assertTrue($constraint->evaluate('barbazbar', '', true));
        $this->assertFalse($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('does not match PCRE pattern "/foo/"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barfoobar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barfoobar' does not match PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintPCRENotMatch2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::matchesRegularExpression('/foo/')
        );

        try {
            $constraint->evaluate('barfoobar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barfoobar' does not match PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringMatches(): void
    {
        $constraint = Assert::matches('*%c*');
        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*.\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches2(): void
    {
        $constraint = Assert::matches('*%s*');
        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[^\r\n]+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches3(): void
    {
        $constraint = Assert::matches('*%i*');
        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[+-]?\d+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches4(): void
    {
        $constraint = Assert::matches('*%d*');
        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*\d+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches5(): void
    {
        $constraint = Assert::matches('*%x*');
        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*0f0f0f*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[0-9a-fA-F]+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches6(): void
    {
        $constraint = Assert::matches('*%f*');
        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*1.0*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringStartsWith(): void
    {
        $constraint = Assert::stringStartsWith('prefix');

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertTrue($constraint->evaluate('prefixfoo', '', true));
        $this->assertEquals('starts with "prefix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'foo' starts with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringStartsWith2(): void
    {
        $constraint = Assert::stringStartsWith('prefix');

        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that 'foo' starts with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringStartsNotWith(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringStartsWith('prefix')
        );

        $this->assertTrue($constraint->evaluate('foo', '', true));
        $this->assertFalse($constraint->evaluate('prefixfoo', '', true));
        $this->assertEquals('starts not with "prefix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('prefixfoo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'prefixfoo' starts not with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringStartsNotWith2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringStartsWith('prefix')
        );

        try {
            $constraint->evaluate('prefixfoo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'prefixfoo' starts not with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringContains(): void
    {
        $constraint = Assert::stringContains('foo');

        $this->assertFalse($constraint->evaluate('barbazbar', '', true));
        $this->assertTrue($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('contains "foo"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barbazbar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barbazbar' contains "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringContainsWhenIgnoreCase(): void
    {
        $constraint = Assert::stringContains('oryginał', true);

        $this->assertFalse($constraint->evaluate('oryginal', '', true));
        $this->assertTrue($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertTrue($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('contains "oryginał"', $constraint->toString());
        $this->assertCount(1, $constraint);

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('oryginal');
    }

    public function testConstraintStringContainsForUtf8StringWhenNotIgnoreCase(): void
    {
        $constraint = Assert::stringContains('oryginał', false);

        $this->assertFalse($constraint->evaluate('oryginal', '', true));
        $this->assertFalse($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertTrue($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('contains "oryginał"', $constraint->toString());
        $this->assertCount(1, $constraint);

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('oryginal');
    }

    public function testConstraintStringContains2(): void
    {
        $constraint = Assert::stringContains('foo');

        try {
            $constraint->evaluate('barbazbar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barbazbar' contains "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringNotContains(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('foo')
        );

        $this->assertTrue($constraint->evaluate('barbazbar', '', true));
        $this->assertFalse($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('does not contain "foo"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barfoobar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barfoobar' does not contain "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringNotContainsWhenIgnoreCase(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('oryginał')
        );

        $this->assertTrue($constraint->evaluate('original', '', true));
        $this->assertFalse($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertFalse($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('does not contain "oryginał"', $constraint->toString());
        $this->assertCount(1, $constraint);

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('ORYGINAŁ');
    }

    public function testConstraintStringNotContainsForUtf8StringWhenNotIgnoreCase(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('oryginał', false)
        );

        $this->assertTrue($constraint->evaluate('original', '', true));
        $this->assertTrue($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertFalse($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('does not contain "oryginał"', $constraint->toString());
        $this->assertCount(1, $constraint);

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('oryginał');
    }

    public function testConstraintStringNotContains2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('foo')
        );

        try {
            $constraint->evaluate('barfoobar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barfoobar' does not contain "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsWith(): void
    {
        $constraint = Assert::stringEndsWith('suffix');

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertTrue($constraint->evaluate('foosuffix', '', true));
        $this->assertEquals('ends with "suffix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'foo' ends with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsWith2(): void
    {
        $constraint = Assert::stringEndsWith('suffix');

        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'foo' ends with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsNotWith(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringEndsWith('suffix')
        );

        $this->assertTrue($constraint->evaluate('foo', '', true));
        $this->assertFalse($constraint->evaluate('foosuffix', '', true));
        $this->assertEquals('ends not with "suffix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foosuffix');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsNotWith2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringEndsWith('suffix')
        );

        try {
            $constraint->evaluate('foosuffix', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayContainsCheckForObjectIdentity(): void
    {
        // Check for primitive type.
        $constraint = new TraversableContains('foo', true, true);

        $this->assertFalse($constraint->evaluate([0], '', true));
        $this->assertFalse($constraint->evaluate([true], '', true));

        // Default case.
        $constraint = new TraversableContains('foo');

        $this->assertTrue($constraint->evaluate([0], '', true));
        $this->assertTrue($constraint->evaluate([true], '', true));
    }

    public function testConstraintArrayContains(): void
    {
        $constraint = new TraversableContains('foo');

        $this->assertFalse($constraint->evaluate(['bar'], '', true));
        $this->assertTrue($constraint->evaluate(['foo'], '', true));
        $this->assertEquals("contains 'foo'", $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(['bar']);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array contains 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayContains2(): void
    {
        $constraint = new TraversableContains('foo');

        try {
            $constraint->evaluate(['bar'], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that an array contains 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotContains(): void
    {
        $constraint = Assert::logicalNot(
            new TraversableContains('foo')
        );

        $this->assertTrue($constraint->evaluate(['bar'], '', true));
        $this->assertFalse($constraint->evaluate(['foo'], '', true));
        $this->assertEquals("does not contain 'foo'", $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(['foo']);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array does not contain 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotContains2(): void
    {
        $constraint = Assert::logicalNot(
            new TraversableContains('foo')
        );

        try {
            $constraint->evaluate(['foo'], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that an array does not contain 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintSplObjectStorageContains(): void
    {
        $object     = new \stdClass;
        $constraint = new TraversableContains($object);
        $this->assertStringMatchesFormat('contains stdClass Object &%s ()', $constraint->toString());

        $storage = new \SplObjectStorage;
        $this->assertFalse($constraint->evaluate($storage, '', true));

        $storage->attach($object);
        $this->assertTrue($constraint->evaluate($storage, '', true));

        try {
            $constraint->evaluate(new \SplObjectStorage);
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
Failed asserting that a traversable contains stdClass Object &%x ().

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintSplObjectStorageContains2(): void
    {
        $object     = new \stdClass;
        $constraint = new TraversableContains($object);

        try {
            $constraint->evaluate(new \SplObjectStorage, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
custom message
Failed asserting that a traversable contains stdClass Object &%x ().

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testAttributeEqualTo(): void
    {
        $object     = new \ClassWithNonPublicAttributes;
        $constraint = Assert::attributeEqualTo('foo', 1);

        $this->assertTrue($constraint->evaluate($object, '', true));
        $this->assertEquals('attribute "foo" is equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        $constraint = Assert::attributeEqualTo('foo', 2);

        $this->assertFalse($constraint->evaluate($object, '', true));

        try {
            $constraint->evaluate($object);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that attribute "foo" is equal to 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testAttributeEqualTo2(): void
    {
        $object     = new \ClassWithNonPublicAttributes;
        $constraint = Assert::attributeEqualTo('foo', 2);

        try {
            $constraint->evaluate($object, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that attribute "foo" is equal to 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testAttributeNotEqualTo(): void
    {
        $object     = new \ClassWithNonPublicAttributes;
        $constraint = Assert::logicalNot(
            Assert::attributeEqualTo('foo', 2)
        );

        $this->assertTrue($constraint->evaluate($object, '', true));
        $this->assertEquals('attribute "foo" is not equal to 2', $constraint->toString());
        $this->assertCount(1, $constraint);

        $constraint = Assert::logicalNot(
            Assert::attributeEqualTo('foo', 1)
        );

        $this->assertFalse($constraint->evaluate($object, '', true));

        try {
            $constraint->evaluate($object);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that attribute "foo" is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testAttributeNotEqualTo2(): void
    {
        $object     = new \ClassWithNonPublicAttributes;
        $constraint = Assert::logicalNot(
            Assert::attributeEqualTo('foo', 1)
        );

        try {
            $constraint->evaluate($object, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that attribute "foo" is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsEmpty(): void
    {
        $constraint = new IsEmpty;

        $this->assertFalse($constraint->evaluate(['foo'], '', true));
        $this->assertTrue($constraint->evaluate([], '', true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject(['foo']), '', true));
        $this->assertTrue($constraint->evaluate(new \ArrayObject([]), '', true));
        $this->assertEquals('is empty', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(['foo']);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array is empty.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsEmpty2(): void
    {
        $constraint = new IsEmpty;

        try {
            $constraint->evaluate(['foo'], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that an array is empty.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintCountWithAnArray(): void
    {
        $constraint = new Count(5);

        $this->assertTrue($constraint->evaluate([1, 2, 3, 4, 5], '', true));
        $this->assertFalse($constraint->evaluate([1, 2, 3, 4], '', true));
    }

    public function testConstraintCountWithAnIteratorWhichDoesNotImplementCountable(): void
    {
        $constraint = new Count(5);

        $this->assertTrue($constraint->evaluate(new \TestIterator([1, 2, 3, 4, 5]), '', true));
        $this->assertFalse($constraint->evaluate(new \TestIterator([1, 2, 3, 4]), '', true));
    }

    public function testConstraintCountWithAnObjectImplementingCountable(): void
    {
        $constraint = new Count(5);

        $this->assertTrue($constraint->evaluate(new \ArrayObject([1, 2, 3, 4, 5]), '', true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject([1, 2, 3, 4]), '', true));
    }

    public function testConstraintCountFailing(): void
    {
        $constraint = new Count(5);

        try {
            $constraint->evaluate([1, 2]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 matches expected size 5.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotCountFailing(): void
    {
        $constraint = Assert::logicalNot(
            new Count(2)
        );

        try {
            $constraint->evaluate([1, 2]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 does not match expected size 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintSameSizeWithAnArray(): void
    {
        $constraint = new SameSize([1, 2, 3, 4, 5]);

        $this->assertTrue($constraint->evaluate([6, 7, 8, 9, 10], '', true));
        $this->assertFalse($constraint->evaluate([1, 2, 3, 4], '', true));
    }

    public function testConstraintSameSizeWithAnIteratorWhichDoesNotImplementCountable(): void
    {
        $constraint = new SameSize(new \TestIterator([1, 2, 3, 4, 5]));

        $this->assertTrue($constraint->evaluate(new \TestIterator([6, 7, 8, 9, 10]), '', true));
        $this->assertFalse($constraint->evaluate(new \TestIterator([1, 2, 3, 4]), '', true));
    }

    public function testConstraintSameSizeWithAnObjectImplementingCountable(): void
    {
        $constraint = new SameSize(new \ArrayObject([1, 2, 3, 4, 5]));

        $this->assertTrue($constraint->evaluate(new \ArrayObject([6, 7, 8, 9, 10]), '', true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject([1, 2, 3, 4]), '', true));
    }

    public function testConstraintSameSizeFailing(): void
    {
        $constraint = new SameSize([1, 2, 3, 4, 5]);

        try {
            $constraint->evaluate([1, 2]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 matches expected size 5.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotSameSizeFailing(): void
    {
        $constraint = Assert::logicalNot(
            new SameSize([1, 2])
        );

        try {
            $constraint->evaluate([3, 4]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 does not match expected size 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintException(): void
    {
        $constraint = new Constraint\Exception('FoobarException');
        $exception  = new \DummyException('Test');
        $stackTrace = Filter::getFilteredStacktrace($exception);

        try {
            $constraint->evaluate($exception);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that exception of type "DummyException" matches expected exception "FoobarException". Message was: "Test" at
$stackTrace.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * Removes spaces in front of newlines
     *
     * @param string $string
     *
     * @return string
     */
    private function trimnl($string)
    {
        return \preg_replace('/[ ]*\n/', "\n", $string);
    }
}
