<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState enabled
 */
class Issue1335Test extends TestCase
{
    public function testGlobalString(): void
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }

    public function testGlobalIntTruthy(): void
    {
        $this->assertEquals(1, $GLOBALS['globalIntTruthy']);
    }

    public function testGlobalIntFalsey(): void
    {
        $this->assertEquals(0, $GLOBALS['globalIntFalsey']);
    }

    public function testGlobalFloat(): void
    {
        $this->assertEquals(1.123, $GLOBALS['globalFloat']);
    }

    public function testGlobalBoolTrue(): void
    {
        $this->assertTrue($GLOBALS['globalBoolTrue']);
    }

    public function testGlobalBoolFalse(): void
    {
        $this->assertFalse($GLOBALS['globalBoolFalse']);
    }

    public function testGlobalNull(): void
    {
        $this->assertEquals(null, $GLOBALS['globalNull']);
    }

    public function testGlobalArray(): void
    {
        $this->assertEquals(['foo'], $GLOBALS['globalArray']);
    }

    public function testGlobalNestedArray(): void
    {
        $this->assertEquals([['foo']], $GLOBALS['globalNestedArray']);
    }

    public function testGlobalObject(): void
    {
        $this->assertEquals((object) ['foo'=> 'bar'], $GLOBALS['globalObject']);
    }

    public function testGlobalObjectWithBackSlashString(): void
    {
        $this->assertEquals((object) ['foo'=> 'back\\slash'], $GLOBALS['globalObjectWithBackSlashString']);
    }

    public function testGlobalObjectWithDoubleBackSlashString(): void
    {
        $this->assertEquals((object) ['foo'=> 'back\\\\slash'], $GLOBALS['globalObjectWithDoubleBackSlashString']);
    }
}
