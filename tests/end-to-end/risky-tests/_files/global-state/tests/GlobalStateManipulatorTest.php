<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\RiskyTests;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\TestFixture\RiskyTests\GlobalStateManipulator
 */
final class GlobalStateManipulatorTest extends TestCase
{
    public function testManipulatesGlobalState(): void
    {
        (new GlobalStateManipulator)->manipulateGlobalState();

        $this->assertSame('bar', $GLOBALS['foo']);
    }
}
