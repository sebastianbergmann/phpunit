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

use DateTimeZone;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class TimeZoneTypesTest extends TestCase
{
    #[Property]
    public function testDateTimeZone(DateTimeZone $value): void
    {
        $this->assertContains($value->getName(), DateTimeZone::listIdentifiers());
    }

    #[Property]
    public function testFalsifiedByTimeZone(DateTimeZone $value): void
    {
        $this->assertNotSame('UTC', $value->getName());
    }
}
