<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;

final class FailedExpectationTest extends TestCase
{
    public function testOne(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->once())->method('doSomething');
    }
}
