<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

use PharIo\Version\VersionConstraintParser;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\VersionComparisonOperator;

/**
 * @covers \PHPUnit\Util\Metadata\VersionComparisonRequirement
 * @covers \PHPUnit\Util\Metadata\VersionConstraintRequirement
 *
 * @uses \PHPUnit\Util\VersionComparisonOperator
 *
 * @small
 */
final class VersionRequirementTest extends TestCase
{
    /**
     * @dataProvider comparisonProvider
     */
    public function testVersionRequirementCanBeCheckedUsingSimpleComparison(bool $expected, string $version, VersionComparisonRequirement $requirement): void
    {
        $this->assertSame($expected, $requirement->isSatisfiedBy($version));
    }

    /**
     * @dataProvider constraintProvider
     */
    public function testVersionRequirementCanBeCheckedUsingVersionConstraint(bool $expected, string $version, VersionConstraintRequirement $requirement): void
    {
        $this->assertSame($expected, $requirement->isSatisfiedBy($version));
    }

    public function comparisonProvider(): array
    {
        return [
            [true, '1.0.0', new VersionComparisonRequirement('1.0.0', new VersionComparisonOperator('='))],
        ];
    }

    public function constraintProvider(): array
    {
        return [
            [
                true,
                '1.0.0',
                new VersionConstraintRequirement(
                    (new VersionConstraintParser)->parse('1.0.0')
                ),
            ],
        ];
    }
}
