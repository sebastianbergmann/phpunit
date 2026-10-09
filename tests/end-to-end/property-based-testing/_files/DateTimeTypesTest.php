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

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class DateTimeTypesTest extends TestCase
{
    #[Property]
    public function testDateTimeImmutable(DateTimeImmutable $value): void
    {
        $this->assertIsPointInTimeInUtcFrom1900To2199($value);
    }

    #[Property]
    public function testDateTimeInterface(DateTimeInterface $value): void
    {
        $this->assertIsPointInTimeInUtcFrom1900To2199($value);
    }

    private function assertIsPointInTimeInUtcFrom1900To2199(DateTimeInterface $value): void
    {
        $this->assertSame(0, $value->getOffset());
        $this->assertGreaterThanOrEqual(new DateTimeImmutable('1900-01-01 00:00:00 UTC'), $value);
        $this->assertLessThanOrEqual(new DateTimeImmutable('2199-12-31 23:59:59 UTC'), $value);
    }
}
