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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class RejectionReasonsTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function evenIntegers(): ValueGenerator
    {
        return Generators::integers(0, 1000)->filter(static fn (int $value): bool => $value % 2 === 0);
    }

    #[Property(examples: 3)]
    public function testRejectsEveryExampleForSeveralReasons(#[ForAll('evenIntegers')] int $value): void
    {
        $this->assume($value < 0);

        $this->assertLessThan(0, $value);
    }

    #[Property]
    public function testRejectsItsOnlyExample(true $value): void
    {
        $this->assume(!$value);

        $this->assertFalse($value);
    }
}
