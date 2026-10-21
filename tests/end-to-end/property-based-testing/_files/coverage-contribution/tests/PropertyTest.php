<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\CoverageContribution;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
final class PropertyTest extends TestCase
{
    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 20)]
    public function testSomeExamplesContributeToCodeCoverage(int $value): void
    {
        if ($value > 5) {
            $this->assertSame($value + $value, Calculator::double($value));

            return;
        }

        $this->assertLessThanOrEqual(5, $value);
    }

    #[Property(examples: 20)]
    public function testNoExampleContributesToCodeCoverage(int $value): void
    {
        $this->assertSame($value, $value);
    }
}
