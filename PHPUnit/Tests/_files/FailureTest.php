<?php
class FailureTest extends PHPUnit_Framework_TestCase
{
    public function testAssertArrayEqualsArray()
    {
        $this->assertEquals(array(1), array(2));
    }

    public function testAssertIntegerEqualsInteger()
    {
        $this->assertEquals(1, 2);
    }

    public function testAssertObjectEqualsObject()
    {
        $a = new StdClass;
        $a->foo = 'bar';

        $b = new StdClass;
        $b->bar = 'foo';

        $this->assertEquals($a, $b);
    }

    public function testAssertNullEqualsString()
    {
        $this->assertEquals(NULL, 'bar');
    }

    public function testAssertStringEqualsString()
    {
        $this->assertEquals('foo', 'bar');
    }

    public function testAssertTextEqualsText()
    {
        $this->assertEquals("foo\nbar\n", "foo\nbaz\n");
    }

    public function testAssertTextSameText()
    {
        $this->assertSame('foo', 'bar');
    }

    public function testAssertObjectSameObject()
    {
        $this->assertSame(new StdClass, new StdClass);
    }

    public function testAssertObjectSameNull()
    {
        $this->assertSame(new StdClass, NULL);
    }
}
?>
