<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class ExceptionInTearDownAfterClassTest extends TestCase
{
    public $setUp                = false;

    public $assertPreConditions  = false;

    public $assertPostConditions = false;

    public $tearDown             = false;

    public $tearDownAfterClass   = false;

    public $testSomething        = false;

    public static function tearDownAfterClass(): void
    {
        throw new Exception('throw Exception in tearDownAfterClass()');
    }

    protected function setUp(): void
    {
        $this->setUp = true;
    }

    protected function tearDown(): void
    {
        $this->tearDown = true;
    }

    public function testOne(): void
    {
        $this->testSomething = true;
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->testSomething = $this->testSomething && true;
        $this->assertTrue(true);
    }

    protected function assertPreConditions(): void
    {
        $this->assertPreConditions = true;
    }

    protected function assertPostConditions(): void
    {
        $this->assertPostConditions = true;
    }
}
