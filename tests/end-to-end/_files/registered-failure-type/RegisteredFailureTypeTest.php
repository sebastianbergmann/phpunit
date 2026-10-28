<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\RegisteredFailureType;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RegisteredFailureTypeTest extends TestCase
{
    protected function setUp(): void
    {
        $this->registerFailureType(RuntimeException::class);
    }

    public function testFailsWithoutPerformingAssertions(): void
    {
        throw new RuntimeException('message');
    }

    public function testFailsAfterPerformingAssertion(): void
    {
        $this->assertTrue(true);

        throw new RuntimeException('message');
    }
}
