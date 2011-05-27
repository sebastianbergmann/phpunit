<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'TestIterator.php';

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class Framework_ConstraintTest extends PHPUnit_Framework_TestCase
{
    /**
     * Removes spaces in front of newlines
     *
     * @param  string $string
     * @return string
     */
    public static function trimnl($string)
    {
        return preg_replace('/[ ]*\n/', "\n", $string);
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintArrayHasKey()
    {
        $constraint = PHPUnit_Framework_Assert::arrayHasKey(0);

        $this->assertFalse($constraint->evaluate(array()));
        $this->assertEquals('has the key <integer:0>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(array(), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that an array has the key <integer:0>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     */
    public function testConstraintArrayHasKey2()
    {
        $constraint = PHPUnit_Framework_Assert::arrayHasKey(0);

        try {
            $constraint->fail(array(), 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message\nFailed asserting that an array has the key <integer:0>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotHasKey()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::arrayHasKey(0)
        );

        $this->assertTrue($constraint->evaluate(array()));
        $this->assertEquals('does not have the key <integer:0>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(array(0), '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that an array does not have the key <integer:0>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotHasKey2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::arrayHasKey(0)
        );

        try {
            $constraint->fail(array(0), 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that an array does not have the key <integer:0>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     * @covers PHPUnit_Framework_Assert::fileExists
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintFileExists()
    {
        $constraint = PHPUnit_Framework_Assert::fileExists();

        $this->assertFalse($constraint->evaluate('foo'));
        $this->assertEquals('file exists', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('foo', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that file "foo" exists.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testConstraintFileExists2()
    {
        $constraint = PHPUnit_Framework_Assert::fileExists();

        try {
            $constraint->fail('foo', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that file "foo" exists.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::logicalNot
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testConstraintFileNotExists()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::fileExists()
        );

        $this->assertTrue($constraint->evaluate('foo'));
        $this->assertEquals('file does not exist', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('foo', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that file "foo" does not exist.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::logicalNot
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testConstraintFileNotExists2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::fileExists()
        );

        try {
            $constraint->fail('foo', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that file "foo" does not exist.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Assert::greaterThan
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintGreaterThan()
    {
        $constraint = PHPUnit_Framework_Assert::greaterThan(1);

        $this->assertFalse($constraint->evaluate(0));
        $this->assertTrue($constraint->evaluate(2));
        $this->assertEquals('is greater than <integer:1>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:0> is greater than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Assert::greaterThan
     */
    public function testConstraintGreaterThan2()
    {
        $constraint = PHPUnit_Framework_Assert::greaterThan(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:0> is greater than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::greaterThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotGreaterThan()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::greaterThan(1)
        );

        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is not greater than <integer:1>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:1> is not greater than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::greaterThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotGreaterThan2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::greaterThan(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:1> is not greater than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Assert::greaterThanOrEqual
     */
    public function testConstraintGreaterThanOrEqual()
    {
        $constraint = PHPUnit_Framework_Assert::greaterThanOrEqual(1);

        $this->assertTrue($constraint->evaluate(1));
        $this->assertFalse($constraint->evaluate(0));
        $this->assertEquals('is equal to <integer:1> or is greater than <integer:1>', $constraint->toString());
        $this->assertEquals(2, count($constraint));

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:0> is equal to <integer:1> or is greater than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Assert::greaterThanOrEqual
     */
    public function testConstraintGreaterThanOrEqual2()
    {
        $constraint = PHPUnit_Framework_Assert::greaterThanOrEqual(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:0> is equal to <integer:1> or is greater than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::greaterThanOrEqual
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotGreaterThanOrEqual()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::greaterThanOrEqual(1)
        );

        $this->assertTrue($constraint->evaluate(0));
        $this->assertEquals('not( is equal to <integer:1> or is greater than <integer:1> )', $constraint->toString());
        $this->assertEquals(2, count($constraint));

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:1> not( is equal to <integer:1> or is greater than <integer:1> ).

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::greaterThanOrEqual
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotGreaterThanOrEqual2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::greaterThanOrEqual(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:1> not( is equal to <integer:1> or is greater than <integer:1> ).

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsAnything
     * @covers PHPUnit_Framework_Assert::anything
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintIsAnything()
    {
        $constraint = PHPUnit_Framework_Assert::anything();

        $this->assertTrue($constraint->evaluate(NULL));
        $this->assertNull($constraint->fail(NULL, ''));
        $this->assertEquals('is anything', $constraint->toString());
        $this->assertEquals(0, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsAnything
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::anything
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotIsAnything()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::anything()
        );

        $this->assertFalse($constraint->evaluate(NULL));
        $this->assertEquals('is not anything', $constraint->toString());
        $this->assertEquals(0, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Assert::equalTo
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintIsEqual()
    {
        $constraint = PHPUnit_Framework_Assert::equalTo(1);

        $this->assertFalse($constraint->evaluate(0));
        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is equal to <integer:1>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:0> matches expected <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function isEqualProvider()
    {
        $a = new stdClass;
        $a->foo = 'bar';
        $b = new stdClass;
        $ahash = spl_object_hash($a);
        $bhash = spl_object_hash($b);

        $c = new stdClass;
        $c->foo = 'bar';
        $c->int = 1;
        $c->array = array(0, array(1), array(2), 3);
        $c->related = new stdClass;
        $c->related->foo = "a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk";
        $c->self = $c;
        $c->c = $c;
        $d = new stdClass;
        $d->foo = 'bar';
        $d->int = 2;
        $d->array = array(0, array(4), array(2), 3);
        $d->related = new stdClass;
        $d->related->foo = "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk";
        $d->self = $d;
        $d->c = $c;

        $storage1 = new SplObjectStorage;
        $storage1->attach($a);
        $storage1->attach($b);
        $storage2 = new SplObjectStorage;
        $storage2->attach($b);

        $dom1 = new DOMDocument;
        $dom1->preserveWhiteSpace = FALSE;
        $dom1->loadXML('<root></root>');
        $dom2 = new DOMDocument;
        $dom2->preserveWhiteSpace = FALSE;
        $dom2->loadXML('<root><foo/></root>');

        return array(
            array(1, 0, <<<EOF
Failed asserting that <integer:0> matches expected <integer:1>.

EOF
            ),
            array(1.1, 0, <<<EOF
Failed asserting that <integer:0> matches expected <double:1.1>.

EOF
            ),
            array('a', 'b', <<<EOF
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-a
+b

EOF
            ),
            array("a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk", "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk", <<<EOF
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
 a
-b
+p

@@ @@
 i
-j
+w
 k

EOF
            ),
            array(1, array(0), <<<EOF
Failed asserting that
Array
(
   [0] => 0
)
matches expected <integer:1>.

EOF
            ),
            array(array(0), 1, <<<EOF
custom message
Failed asserting that <integer:1> is equal to
Array
(
    [0] => 0
)
.
custom message

EOF
            ),
            array(array(0), array(1), <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array
 (
-    [0] => 0
+    [0] => 1
 )

EOF
            ),
            array(array(0, array(1), array(2), 3), array(0, array(4), array(2), 3), <<<EOF
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array
 (
     [0] => 0
     [1] => Array
         (
-            [0] => 1
+            [0] => 4
         )

     [2] => Array
         (
             [0] => 2
         )

     [3] => 3
 )

EOF
            ),
            array($a, array(0), <<<EOF
custom message
Failed asserting that
Array
(
    [0] => 0
)
 is equal to
stdClass Object
(
    [foo] => bar
)
.
custom message

EOF
            ),
            array(array(0), $a, <<<EOF
custom message
Failed asserting that
stdClass Object
(
    [foo] => bar
)
 is equal to
Array
(
    [0] => 0
)
.
custom message

EOF
            ),
            array($a, $b, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object
 (
-    [foo] => bar
 )

EOF
            ),
            array($c, $d, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object
 (
     [foo] => bar
-    [int] => 1
+    [int] => 2
     [array] => Array
         (
             [0] => 0
             [1] => Array
                 (
-                    [0] => 1
+                    [0] => 4

@@ @@
             [foo] => a
-b
+p

@@ @@
 i
-j
+w

@@ @@
     [c] => stdClass Object
+        (
+            [foo] => bar
+            [int] => 1
+            [array] => Array
+                (
+                    [0] => 0
+                    [1] => Array
+                        (
+                            [0] => 1
+                        )
+
+                    [2] => Array
+                        (
+                            [0] => 2
+                        )
+
+                    [3] => 3
+                )
+
+            [related] => stdClass Object
+                (
+                    [foo] => a
+b
+c
+d
+e
+f
+g
+h
+i
+j
+k
+                )
+
+            [self] => stdClass Object
  *RECURSION*
+            [c] => stdClass Object
+ *RECURSION*
+        )
+
 )

EOF
            ),
            array($storage1, $storage2, <<<EOF
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 SplObjectStorage Object
 (
     [storage:SplObjectStorage:private] => Array
         (
-            [$ahash] => Array
-                (
-                    [obj] => stdClass Object
-                        (
-                            [foo] => bar
-                        )
-
-                    [inf] =>
-                )
-
             [$bhash] => Array
                 (
                     [obj] => stdClass Object
                         (
                         )

                     [inf] =>
                 )

         )

 )

EOF
            ),
            array($dom1, $dom2, <<<EOF
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
 <?xml version="1.0"?>
-<root/>
+<root>
+  <foo/>
+</root>

EOF
            ),
        );
    }

    /**
     * @dataProvider isEqualProvider
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Assert::equalTo
     */
    public function testConstraintIsEqual2($expected, $actual, $message)
    {
        $constraint = PHPUnit_Framework_Assert::equalTo($expected);

        try {
            $constraint->fail($actual, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\n$message",
              self::trimnl(PHPUnit_Framework_TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::equalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotEqual()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::equalTo(1)
        );

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(1));
        $this->assertEquals('is not equal to <integer:1>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:1> is not equal to <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::equalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotEqual2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::equalTo(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:1> is not equal to <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     * @covers PHPUnit_Framework_Assert::identicalTo
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintIsIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = PHPUnit_Framework_Assert::identicalTo($a);

        $this->assertFalse($constraint->evaluate($b));
        $this->assertTrue($constraint->evaluate($a));
        $this->assertEquals('is identical to an object of class "stdClass"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail($b, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that two variables reference the same object.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     * @covers PHPUnit_Framework_Assert::identicalTo
     */
    public function testConstraintIsIdentical2()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = PHPUnit_Framework_Assert::identicalTo($a);

        try {
            $constraint->fail($b, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that two variables reference the same object.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::identicalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::identicalTo($a)
        );

        $this->assertTrue($constraint->evaluate($b));
        $this->assertFalse($constraint->evaluate($a));
        $this->assertEquals('is not identical to an object of class "stdClass"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail($a, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that
stdClass Object
(
)
 is not identical to an object of class "stdClass".

EOF
              ,
              self::trimnl(PHPUnit_Framework_TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::identicalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotIdentical2()
    {
        $a = new stdClass;

        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::identicalTo($a)
        );

        try {
            $constraint->fail($a, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that
stdClass Object
(
)
 is not identical to an object of class "stdClass".

EOF
              ,
              self::trimnl(PHPUnit_Framework_TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintIsInstanceOf()
    {
        $constraint = PHPUnit_Framework_Assert::isInstanceOf('Exception');

        $this->assertFalse($constraint->evaluate(new stdClass));
        $this->assertTrue($constraint->evaluate(new Exception));
        $this->assertEquals('is instance of class "Exception"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <stdClass> is an instance of class "Exception".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     */
    public function testConstraintIsInstanceOf2()
    {
        $constraint = PHPUnit_Framework_Assert::isInstanceOf('Exception');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that <stdClass> is an instance of class "Exception".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotInstanceOf()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isInstanceOf('stdClass')
        );

        $this->assertFalse($constraint->evaluate(new stdClass));
        $this->assertTrue($constraint->evaluate(new Exception));
        $this->assertEquals('is not instance of class "stdClass"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(new stdClass, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <stdClass> is not an instance of class "stdClass".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotInstanceOf2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isInstanceOf('stdClass')
        );

        try {
            $constraint->fail(new stdClass, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that <stdClass> is not an instance of class "stdClass".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     * @covers PHPUnit_Framework_Assert::isType
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintIsType()
    {
        $constraint = PHPUnit_Framework_Assert::isType('string');

        $this->assertFalse($constraint->evaluate(0));
        $this->assertTrue($constraint->evaluate(''));
        $this->assertEquals('is of type "string"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
Failed asserting that
stdClass Object
(
)
 is of type "string".

EOF
              ,
              self::trimnl(PHPUnit_Framework_TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     * @covers PHPUnit_Framework_Assert::isType
     */
    public function testConstraintIsType2()
    {
        $constraint = PHPUnit_Framework_Assert::isType('string');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that
stdClass Object
(
)
 is of type "string".

EOF
              ,
              self::trimnl(PHPUnit_Framework_TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::isType
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotType()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isType('string')
        );

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(''));
        $this->assertEquals('is not of type "string"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:> is not of type "string".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::isType
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotType2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isType('string')
        );

        try {
            $constraint->fail('', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that <string:> is not of type "string".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Assert::lessThan
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintLessThan()
    {
        $constraint = PHPUnit_Framework_Assert::lessThan(1);

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(2));
        $this->assertEquals('is less than <integer:1>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:0> is less than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Assert::lessThan
     */
    public function testConstraintLessThan2()
    {
        $constraint = PHPUnit_Framework_Assert::lessThan(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:0> is less than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::lessThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotLessThan()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::lessThan(1)
        );

        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is not less than <integer:1>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:1> is not less than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::lessThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotLessThan2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::lessThan(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:1> is not less than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Assert::lessThanOrEqual
     */
    public function testConstraintLessThanOrEqual()
    {
        $constraint = PHPUnit_Framework_Assert::lessThanOrEqual(1);

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(2));
        $this->assertEquals('is equal to <integer:1> or is less than <integer:1>', $constraint->toString());
        $this->assertEquals(2, count($constraint));

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:0> is equal to <integer:1> or is less than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Assert::lessThanOrEqual
     */
    public function testConstraintLessThanOrEqual2()
    {
        $constraint = PHPUnit_Framework_Assert::lessThanOrEqual(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:0> is equal to <integer:1> or is less than <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::lessThanOrEqual
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotLessThanOrEqual()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::lessThanOrEqual(1)
        );

        $this->assertTrue($constraint->evaluate(2));
        $this->assertEquals('not( is equal to <integer:1> or is less than <integer:1> )', $constraint->toString());
        $this->assertEquals(2, count($constraint));

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <integer:1> not( is equal to <integer:1> or is less than <integer:1> ).

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Or
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::lessThanOrEqual
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotLessThanOrEqual2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::lessThanOrEqual(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <integer:1> not( is equal to <integer:1> or is less than <integer:1> ).

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasAttribute
     * @covers PHPUnit_Framework_Assert::classHasAttribute
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintClassHasAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::classHasAttribute('foo');

        $this->assertFalse($constraint->evaluate('stdClass'));
        $this->assertEquals('has attribute "foo"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('stdClass', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that class "stdClass" has attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasAttribute
     * @covers PHPUnit_Framework_Assert::classHasAttribute
     */
    public function testConstraintClassHasAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::classHasAttribute('foo');

        try {
            $constraint->fail('stdClass', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "stdClass" has attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::classHasAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintClassNotHasAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::classHasAttribute('notExistingAttribute')
        );

        $this->assertTrue($constraint->evaluate('ClassWithNonPublicAttributes'));
        $this->assertEquals('does not have attribute "notExistingAttribute"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('ClassWithNonPublicAttributes', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "notExistingAttribute".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::classHasAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintClassNotHasAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::classHasAttribute('notExistingAttribute')
        );

        try {
            $constraint->fail('ClassWithNonPublicAttributes', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "notExistingAttribute".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasStaticAttribute
     * @covers PHPUnit_Framework_Assert::classHasStaticAttribute
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintClassHasStaticAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::classHasStaticAttribute('foo');

        $this->assertFalse($constraint->evaluate('stdClass'));
        $this->assertEquals('has static attribute "foo"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('stdClass', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that class "stdClass" has static attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasStaticAttribute
     * @covers PHPUnit_Framework_Assert::classHasStaticAttribute
     */
    public function testConstraintClassHasStaticAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::classHasStaticAttribute('foo');

        try {
            $constraint->fail('stdClass', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "stdClass" has static attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasStaticAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::classHasStaticAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintClassNotHasStaticAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::classHasStaticAttribute('notExistingAttribute')
        );

        $this->assertTrue($constraint->evaluate('ClassWithNonPublicAttributes'));
        $this->assertEquals('does not have static attribute "notExistingAttribute"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('ClassWithNonPublicAttributes', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "notExistingAttribute".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ClassHasStaticAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::classHasStaticAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintClassNotHasStaticAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::classHasStaticAttribute('notExistingAttribute')
        );

        try {
            $constraint->fail('ClassWithNonPublicAttributes', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "notExistingAttribute".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintObjectHasAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::objectHasAttribute('foo');

        $this->assertFalse($constraint->evaluate(new stdClass));
        $this->assertEquals('has attribute "foo"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that object of class "stdClass" has attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     */
    public function testConstraintObjectHasAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::objectHasAttribute('foo');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that object of class "stdClass" has attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintObjectNotHasAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::objectHasAttribute('foo')
        );

        $this->assertTrue($constraint->evaluate(new stdClass));
        $this->assertEquals('does not have attribute "foo"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        $o = new stdClass;
        $o->foo = 'bar';

        try {
            $constraint->fail($o, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that object of class "stdClass" does not have attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintObjectNotHasAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::objectHasAttribute('foo')
        );

        $o = new stdClass;
        $o->foo = 'bar';

        try {
            $constraint->fail($o, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that object of class "stdClass" does not have attribute "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintPCREMatch()
    {
        $constraint = PHPUnit_Framework_Assert::matchesRegularExpression('/foo/');

        $this->assertFalse($constraint->evaluate('barbazbar'));
        $this->assertTrue($constraint->evaluate('barfoobar'));
        $this->assertEquals('matches PCRE pattern "/foo/"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('barbazbar', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:barbazbar> matches PCRE pattern "/foo/".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     */
    public function testConstraintPCREMatch2()
    {
        $constraint = PHPUnit_Framework_Assert::matchesRegularExpression('/foo/');

        try {
            $constraint->fail('barbazbar', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that <string:barbazbar> matches PCRE pattern "/foo/".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintPCRENotMatch()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::matchesRegularExpression('/foo/')
        );

        $this->assertTrue($constraint->evaluate('barbazbar'));
        $this->assertFalse($constraint->evaluate('barfoobar'));
        $this->assertEquals('does not match PCRE pattern "/foo/"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('barfoobar', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:barfoobar> does not match PCRE pattern "/foo/".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintPCRENotMatch2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::matchesRegularExpression('/foo/')
        );

        try {
            $constraint->fail('barfoobar', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(<<<EOF
custom message
Failed asserting that <string:barfoobar> does not match PCRE pattern "/foo/".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringMatches
     * @covers PHPUnit_Framework_Assert::matches
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringMatches()
    {
        $constraint = PHPUnit_Framework_Assert::matches('*%c*');
        $this->assertFalse($constraint->evaluate('**'));
        $this->assertTrue($constraint->evaluate('***'));
        $this->assertEquals('matches PCRE pattern "/^\*.\*$/s"', $constraint->toString());
        $this->assertEquals(1, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringMatches
     * @covers PHPUnit_Framework_Assert::matches
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringMatches2()
    {
        $constraint = PHPUnit_Framework_Assert::matches('*%s*');
        $this->assertFalse($constraint->evaluate('**'));
        $this->assertTrue($constraint->evaluate('***'));
        $this->assertEquals('matches PCRE pattern "/^\*[^\r\n]+\*$/s"', $constraint->toString());
        $this->assertEquals(1, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringMatches
     * @covers PHPUnit_Framework_Assert::matches
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringMatches3()
    {
        $constraint = PHPUnit_Framework_Assert::matches('*%i*');
        $this->assertFalse($constraint->evaluate('**'));
        $this->assertTrue($constraint->evaluate('*0*'));
        $this->assertEquals('matches PCRE pattern "/^\*[+-]?\d+\*$/s"', $constraint->toString());
        $this->assertEquals(1, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringMatches
     * @covers PHPUnit_Framework_Assert::matches
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringMatches4()
    {
        $constraint = PHPUnit_Framework_Assert::matches('*%d*');
        $this->assertFalse($constraint->evaluate('**'));
        $this->assertTrue($constraint->evaluate('*0*'));
        $this->assertEquals('matches PCRE pattern "/^\*\d+\*$/s"', $constraint->toString());
        $this->assertEquals(1, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringMatches
     * @covers PHPUnit_Framework_Assert::matches
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringMatches5()
    {
        $constraint = PHPUnit_Framework_Assert::matches('*%x*');
        $this->assertFalse($constraint->evaluate('**'));
        $this->assertTrue($constraint->evaluate('*0f0f0f*'));
        $this->assertEquals('matches PCRE pattern "/^\*[0-9a-fA-F]+\*$/s"', $constraint->toString());
        $this->assertEquals(1, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringMatches
     * @covers PHPUnit_Framework_Assert::matches
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringMatches6()
    {
        $constraint = PHPUnit_Framework_Assert::matches('*%f*');
        $this->assertFalse($constraint->evaluate('**'));
        $this->assertTrue($constraint->evaluate('*1.0*'));
        $this->assertEquals('matches PCRE pattern "/^\*[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?\*$/s"', $constraint->toString());
        $this->assertEquals(1, count($constraint));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringStartsWith()
    {
        $constraint = PHPUnit_Framework_Assert::stringStartsWith('prefix');

        $this->assertFalse($constraint->evaluate('foo'));
        $this->assertTrue($constraint->evaluate('prefixfoo'));
        $this->assertEquals('starts with "prefix"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('foo', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:foo> starts with "prefix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     */
    public function testConstraintStringStartsWith2()
    {
        $constraint = PHPUnit_Framework_Assert::stringStartsWith('prefix');

        try {
            $constraint->fail('foo', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message\nFailed asserting that <string:foo> starts with "prefix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringStartsNotWith()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringStartsWith('prefix')
        );

        $this->assertTrue($constraint->evaluate('foo'));
        $this->assertFalse($constraint->evaluate('prefixfoo'));
        $this->assertEquals('starts not with "prefix"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('prefixfoo', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:prefixfoo> starts not with "prefix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     */
    public function testConstraintStringStartsNotWith2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringStartsWith('prefix')
        );

        try {
            $constraint->fail('prefixfoo', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <string:prefixfoo> starts not with "prefix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     * @covers PHPUnit_Framework_Assert::stringContains
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringContains()
    {
        $constraint = PHPUnit_Framework_Assert::stringContains('foo');

        $this->assertFalse($constraint->evaluate('barbazbar'));
        $this->assertTrue($constraint->evaluate('barfoobar'));
        $this->assertEquals('contains "foo"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('barbazbar', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:barbazbar> contains "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     * @covers PHPUnit_Framework_Assert::stringContains
     */
    public function testConstraintStringContains2()
    {
        $constraint = PHPUnit_Framework_Assert::stringContains('foo');

        try {
            $constraint->fail('barbazbar', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <string:barbazbar> contains "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::stringContains
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringNotContains()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringContains('foo')
        );

        $this->assertTrue($constraint->evaluate('barbazbar'));
        $this->assertFalse($constraint->evaluate('barfoobar'));
        $this->assertEquals('does not contain "foo"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('barfoobar', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:barfoobar> does not contain "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::stringContains
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringNotContains2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringContains('foo')
        );

        try {
            $constraint->fail('barfoobar', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <string:barfoobar> does not contain "foo".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintStringEndsWith()
    {
        $constraint = PHPUnit_Framework_Assert::stringEndsWith('suffix');

        $this->assertFalse($constraint->evaluate('foo'));
        $this->assertTrue($constraint->evaluate('foosuffix'));
        $this->assertEquals('ends with "suffix"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('foo', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:foo> ends with "suffix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     */
    public function testConstraintStringEndsWith2()
    {
        $constraint = PHPUnit_Framework_Assert::stringEndsWith('suffix');

        try {
            $constraint->fail('foo', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <string:foo> ends with "suffix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringEndsNotWith()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringEndsWith('suffix')
        );

        $this->assertTrue($constraint->evaluate('foo'));
        $this->assertFalse($constraint->evaluate('foosuffix'));
        $this->assertEquals('ends not with "suffix"', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail('foosuffix', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that <string:foosuffix> ends not with "suffix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     */
    public function testConstraintStringEndsNotWith2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringEndsWith('suffix')
        );

        try {
            $constraint->fail('foosuffix', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that <string:foosuffix> ends not with "suffix".

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintArrayContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains('foo');

        $this->assertFalse($constraint->evaluate(array('bar')));
        $this->assertTrue($constraint->evaluate(array('foo')));
        $this->assertEquals('contains <string:foo>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(array('bar'), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that an array contains <string:foo>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     */
    public function testConstraintArrayContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains('foo');

        try {
            $constraint->fail(array('bar'), 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that an array contains <string:foo>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotContains()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          new PHPUnit_Framework_Constraint_TraversableContains('foo')
        );

        $this->assertTrue($constraint->evaluate(array('bar')));
        $this->assertFalse($constraint->evaluate(array('foo')));
        $this->assertEquals('does not contain <string:foo>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(array('foo'), '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that an array does not contain <string:foo>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotContains2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          new PHPUnit_Framework_Constraint_TraversableContains('foo')
        );

        try {
            $constraint->fail(array('foo'), 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message
Failed asserting that an array does not contain <string:foo>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintSplObjectStorageContains()
    {
        $object     = new StdClass;
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains($object);
        $this->assertEquals("contains \nstdClass Object\n(\n)\n", $constraint->toString());

        $storage = new SplObjectStorage;
        $this->assertFalse($constraint->evaluate($storage));

        $storage->attach($object);
        $this->assertTrue($constraint->evaluate($storage));

        try {
            $constraint->fail(new SplObjectStorage, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that an iterator contains \nstdClass Object\n(\n)\n.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     */
    public function testConstraintSplObjectStorageContains2()
    {
        $object     = new StdClass;
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains($object);

        try {
            $constraint->fail(new SplObjectStorage, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message\nFailed asserting that an iterator contains \nstdClass Object\n(\n)\n.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::attributeEqualTo
     * @covers PHPUnit_Framework_Constraint_Attribute
     */
    public function testAttributeEqualTo()
    {
        $object     = new ClassWithNonPublicAttributes;
        $constraint = PHPUnit_Framework_Assert::attributeEqualTo('foo', 1);

        $this->assertTrue($constraint->evaluate($object));
        $this->assertEquals('attribute "foo" is equal to <integer:1>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        $constraint = PHPUnit_Framework_Assert::attributeEqualTo('foo', 2);

        try {
            $constraint->fail($object, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that attribute "foo" is equal to <integer:2>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::attributeEqualTo
     * @covers PHPUnit_Framework_Constraint_Attribute
     */
    public function testAttributeEqualTo2()
    {
        $object     = new ClassWithNonPublicAttributes;
        $constraint = PHPUnit_Framework_Assert::attributeEqualTo('foo', 2);

        try {
            $constraint->fail($object, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message\nFailed asserting that attribute "foo" is equal to <integer:2>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::attributeEqualTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     * @covers PHPUnit_Framework_Constraint_Attribute
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testAttributeNotEqualTo()
    {
        $object     = new ClassWithNonPublicAttributes;
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::attributeEqualTo('foo', 2)
        );

        $this->assertTrue($constraint->evaluate($object));
        $this->assertEquals('attribute "foo" is not equal to <integer:2>', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::attributeEqualTo('foo', 1)
        );

        try {
            $constraint->fail($object, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that attribute "foo" is not equal to <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::attributeEqualTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     * @covers PHPUnit_Framework_Constraint_Attribute
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testAttributeNotEqualTo2()
    {
        $object     = new ClassWithNonPublicAttributes;
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::attributeEqualTo('foo', 1)
        );

        try {
            $constraint->fail($object, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message\nFailed asserting that attribute "foo" is not equal to <integer:1>.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEmpty
     * @covers PHPUnit_Framework_Constraint::count
     */
    public function testConstraintIsEmpty()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEmpty;

        $this->assertFalse($constraint->evaluate(array('foo')));
        $this->assertTrue($constraint->evaluate(array()));
        $this->assertEquals('is empty', $constraint->toString());
        $this->assertEquals(1, count($constraint));

        try {
            $constraint->fail(array('foo'), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Failed asserting that an array is empty.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEmpty
     */
    public function testConstraintIsEmpty2()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEmpty;

        try {
            $constraint->fail(array('foo'), 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
custom message\nFailed asserting that an array is empty.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_Count
     */
    public function testConstraintCountWithAnArray()
    {
        $constraint = new PHPUnit_Framework_Constraint_Count(5);

        $this->assertTrue($constraint->evaluate(array(1,2,3,4,5)));
        $this->assertFalse($constraint->evaluate(array(1,2,3,4)));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_Count
     */
    public function testConstraintCountWithAnIteratorWhichDoesNotImplementCountable()
    {
        $constraint = new PHPUnit_Framework_Constraint_Count(5);

        $this->assertTrue($constraint->evaluate(new TestIterator(array(1,2,3,4,5))));
        $this->assertFalse($constraint->evaluate(new TestIterator(array(1,2,3,4))));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_Count
     */
    public function testConstraintCountWithAnObjectImplementingCountable()
    {
        $constraint = new PHPUnit_Framework_Constraint_Count(5);

        $this->assertTrue($constraint->evaluate(new ArrayObject(array(1,2,3,4,5))));
        $this->assertFalse($constraint->evaluate(new ArrayObject(array(1,2,3,4))));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_Count
     */
    public function testConstraintCountFailing()
    {
        $constraint = new PHPUnit_Framework_Constraint_Count(5);

        try {
            $constraint->fail(array(1,2), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              <<<EOF
Actual size 2 does not match expected size 5.

EOF
              ,
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
