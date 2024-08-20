<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'fail')]
#[TestDox('fail()')]
#[Small]
final class failTest extends TestCase
{
    public function testFailsTest(): void
    {
        $message = 'message';

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);

        $this->fail($message);
    }
}
