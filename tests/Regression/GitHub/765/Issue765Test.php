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

class Issue765Test extends TestCase
{
    public function testDependee(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @depends testDependee
     * @dataProvider dependentProvider
     *
     * @param mixed $a
     */
    public function testDependent($a): void
    {
        $this->assertTrue(true);
    }

    public function dependentProvider(): void
    {
        throw new Exception;
    }
}
