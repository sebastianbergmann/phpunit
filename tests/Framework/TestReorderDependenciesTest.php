<?php
/**
 * This file is part of PHPUnit.
 *
 * @author Ewout Pieter den Ouden <epdouden@gmail.com>
 * @group breakfast
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

class TestReorderDependenciesTest extends TestCase
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
     * @depends test3
     * @group breakfast
     */
    public function test2()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group breakfast
     */
    public function test3()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group breakfast
     */
    public function test_no_dependency()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends test1
     * @group breakfast
     */
    public function test4()
    {
        $this->assertTrue(true);
    }
}
