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

use function PHPUnit\Framework\atLeast;
use PHPUnit\Framework\TestCase;

interface GlobalAtLeastFunctionWithNonPositiveArgumentInterface
{
    public function doSomething(): void;
}

final class GlobalAtLeastFunctionWithNonPositiveArgumentTest extends TestCase
{
    public function testAtLeastWithZero(): void
    {
        $mock = $this->createMock(GlobalAtLeastFunctionWithNonPositiveArgumentInterface::class);

        $mock
            ->expects(atLeast(0))
            ->method('doSomething');

        $this->assertTrue(true);
    }
}
