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
 * @runClassInSeparateProcess
 * @preserveGlobalState enabled
 */
class Issue2591_SeparateClassPreserveTest extends TestCase
{
    public function testOriginalGlobalString(): void
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }

    public function testChangedGlobalString(): void
    {
        $value = 'Hello! I am changed from inside!';

        $GLOBALS['globalString'] = $value;
        $this->assertEquals($value, $GLOBALS['globalString']);
    }

    public function testGlobalString(): void
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }
}
