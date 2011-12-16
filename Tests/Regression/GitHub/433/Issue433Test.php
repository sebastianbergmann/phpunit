<?php

class Issue433Test extends PHPUnit_Framework_TestCase {

    public function testOutputWithExpectationBefore() {
        $this->expectOutputString('test');
        echo 'test';
    }

    public function testOutputWithExpectationAfter() {
        echo 'test';
        $this->expectOutputString('test');
    }

    public function testNotMatchingOutput() {
        echo 'bar';
        $this->expectOutputString('foo');
    }

}

