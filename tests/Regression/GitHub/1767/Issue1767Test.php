<?php
class Issue1767Test extends PHPUnit_Framework_TestCase
{
    function testTrigger() {
        $this->fail("This test will skip the next good test from JUnit xml report");
    }

    /**
     * @depends testTrigger
     */
    function testSkipper() {
        $this->fail("This test should be skipped from JUnit xml report");
    }

    function testShouldNotBeSkipped() {
        $this->fail("This test SHOULD NOT be skipped from JUnit xml report");
    }

    function testAreNotSkipped() {
        $this->fail("This is the next failing test showing up in JUnit xml report");
    }
}