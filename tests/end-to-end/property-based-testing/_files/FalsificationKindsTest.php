<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use Countable;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FalsificationKindsTest extends TestCase
{
    private ?int $valueCheckedInTearDown = null;

    protected function tearDown(): void
    {
        if ($this->valueCheckedInTearDown !== null) {
            $this->assertLessThanOrEqual(100, $this->valueCheckedInTearDown);
        }
    }

    /**
     * @param list<int> $values
     */
    #[Property]
    public function testFailedAssertion(array $values): void
    {
        foreach ($values as $value) {
            $this->assertGreaterThanOrEqual(0, $value);
        }
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testException(int $value): void
    {
        if ($value > 100) {
            throw new RuntimeException('value is too large');
        }

        $this->assertLessThanOrEqual(100, $value);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testError(int $value): void
    {
        if ($value > 100) {
            $this->expectsString($value);
        }

        $this->assertLessThanOrEqual(100, $value);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testUnmetExpectationForATestDouble(int $value): void
    {
        $countable = $this->createMock(Countable::class);

        $countable->expects($this->once())->method('count');

        if ($value <= 100) {
            $countable->count();
        }
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testFailureInTearDown(int $value): void
    {
        $this->valueCheckedInTearDown = $value;

        $this->assertIsInt($value);
    }

    private function expectsString(string $value): void
    {
    }
}
