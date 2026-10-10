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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

#[CoversClass(Money::class)]
#[CoversClass(Money::class)]
final class FalsifiedPropertyWithDuplicateCoverageTargetTest extends TestCase
{
    #[Property]
    public function testIsLessThanOneThousand(int $value): void
    {
        $this->assertLessThan(1000, $value);
    }
}
