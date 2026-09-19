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

interface AnyInvokedCountInterface
{
    public function doSomething(): void;
}

final class AnyInvokedCountTest extends TestCase
{
    public function testAny(): void
    {
        $mock = $this->createMock(AnyInvokedCountInterface::class);

        $mock
            ->expects($this->any())
            ->method('doSomething');

        $this->assertTrue(true);
    }
}
