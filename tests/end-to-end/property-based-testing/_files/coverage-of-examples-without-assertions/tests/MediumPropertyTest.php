<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
#[Medium]
final class MediumPropertyTest extends TestCase
{
    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 20)]
    public function testExecutesCodeThatIsNotCoveredOnlyInExamplesWithoutAssertions(int $value): void
    {
        if ($value > 5) {
            Doubler::double($value);

            return;
        }

        $this->assertSame(2 * $value, Calculator::double($value));
    }
}
