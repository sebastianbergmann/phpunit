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

class DependencyFailureTest extends TestCase
{
    public function testOne(): void
    {
        $this->fail();
    }

    /**
     * @depends testOne
     */
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @depends !clone testTwo
     */
    public function testThree(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @depends clone testOne
     */
    public function testFour(): void
    {
        $this->assertTrue(true);
    }

    /**
     * This test has been added to check the printed warnings for the user
     * when a dependency simply doesn't exist.
     *
     * @depends doesNotExist
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/3517
     */
    public function testHandlesDependsAnnotationForNonexistentTests(): void
    {
        $this->assertTrue(true);
    }
}
