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
use PHPUnit\Framework\TestCase;

final class ExamplesMultiplierTest extends TestCase
{
    #[Property]
    public function testWithConfiguredNumberOfExamples(int $value): void
    {
        $this->assertIsInt($value);
    }

    #[Property(examples: 4)]
    public function testWithSpecifiedNumberOfExamples(int $value): void
    {
        $this->assertIsInt($value);
    }
}
