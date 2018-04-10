<?php

class TestReorderDependencies extends PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @group breakfast
     */
    public function test1() {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends test1
     * @group breakfast
     */
    public function test2() {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends test2
     * @group breakfast
     * @group incompletebreakfast
     */
    public function test3() {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group breakfast
     * @group incompletebreakfast
     */
    public function test_no_dependency() {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends test3
     * @group breakfast
     * @group incompletebreakfast
     */
    public function test4() {
        $this->markTestSkipped();
        $this->assertTrue(true);
    }
}
