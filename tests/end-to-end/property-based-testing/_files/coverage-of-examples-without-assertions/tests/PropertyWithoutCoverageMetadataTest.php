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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class PropertyWithoutCoverageMetadataTest extends TestCase
{
    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 20)]
    public function testExecutesCodeOnlyInExamplesWithoutAssertions(int $value): void
    {
        if ($value > 5) {
            Calculator::double($value);

            return;
        }

        $this->assertLessThanOrEqual(5, $value);
    }
}
