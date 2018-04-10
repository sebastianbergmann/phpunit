<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TestReorderDependencies extends PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @group breakfast
     */
    public function test1()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends test1
     * @group breakfast
     */
    public function test2()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends test2
     * @group breakfast
     * @group incompletebreakfast
     */
    public function test3()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group breakfast
     * @group incompletebreakfast
     */
    public function test_no_dependency()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends test3
     * @group breakfast
     * @group incompletebreakfast
     */
    public function test4()
    {
        $this->markTestSkipped();
        $this->assertTrue(true);
    }
}
