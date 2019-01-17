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
class Issue2591_SeparateFunctionPreserveTest extends TestCase
{
    public function testChangedGlobalString(): void
    {
        $GLOBALS['globalString'] = 'Hello!';
        $this->assertEquals('Hello!', $GLOBALS['globalString']);
    }

    public function testGlobalString(): void
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }
}
