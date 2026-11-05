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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;

final class DrawnValuesRejectedTest extends TestCase
{
    #[Property(examples: 2)]
    public function testDrawsValuesThatCannotBeGenerated(): void
    {
        $value = $this->draw(Generators::integers()->filter(static fn (int $value): bool => false));

        $this->assertIsInt($value);
    }
}
