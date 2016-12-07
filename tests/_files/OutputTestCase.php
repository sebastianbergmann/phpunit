<?php
class OutputTestCase extends PHPUnit_Framework_TestCase
{
    public $setUp = false;

    public function setUp()
    {
        $this->setUp = true;
    }

    protected function tearDown()
    {
        $this->setUp = false;
    }

    public function testExpectOutputStringFooActualFoo()
    {
        $this->expectOutputString('foo');
        print 'foo';
    }

    public function testExpectOutputStringFooActualBar()
    {
        $this->expectOutputString('foo');
        print 'bar';
    }

    public function testExpectOutputRegexFooActualFoo()
    {
        $this->expectOutputRegex('/foo/');
        print 'foo';
    }

    public function testExpectOutputRegexFooActualBar()
    {
        $this->expectOutputRegex('/foo/');
        print 'bar';
    }

    public function testSetOutputCallbackAssertSameFooActualFoo()
    {
        $this->setOutputCallback(function ($output) {
            $this->assertSame('foo', $output);
        });
        print 'foo';
    }

    public function testSetOutputCallbackAssertSameFooActualBar()
    {
        $this->setOutputCallback(function ($output) {
            $this->assertSame('foo', $output);
        });
        print 'bar';
    }

    public function testMultipleExpectOutputFooActualFoo()
    {
        $this->expectOutputRegex('/foo/');
        $this->expectOutputString('foo');
        $this->setOutputCallback(function ($output) {
            $this->assertSame('<info>foo</info>', $output);

            return strip_tags($output);
        });
        print '<info>foo</info>';
    }

    public function testSetOutputCallbackAssertSetUp()
    {
        $this->setOutputCallback(function () {
            $this->assertTrue($this->setUp);
        });
    }
}
