<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use PharIo\Version\VersionConstraintParser;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\VersionComparisonOperator;

/**
 * @covers \PHPUnit\Metadata\VersionComparisonRequirement
 * @covers \PHPUnit\Metadata\VersionConstraintRequirement
 * @covers \PHPUnit\Metadata\VersionRequirement
 *
 * @uses \PHPUnit\Util\VersionComparisonOperator
 *
 * @small
 */
final class VersionRequirementTest extends TestCase
{
    public function testCanBeCreatedFromStringWithVersionConstraint(): void
    {
        $requirement = VersionRequirement::from('^1.0');

        $this->assertInstanceOf(VersionConstraintRequirement::class, $requirement);
        $this->assertSame('^1.0', $requirement->asString());
    }

    /**
     * @dataProvider constraintProvider
     */
    public function testVersionRequirementCanBeCheckedUsingVersionConstraint(bool $expected, string $version, VersionConstraintRequirement $requirement): void
    {
        $this->assertSame($expected, $requirement->isSatisfiedBy($version));
    }

    public function testCanBeCreatedFromStringWithSimpleComparison(): void
    {
        $requirement = VersionRequirement::from('>= 1.0');

        $this->assertInstanceOf(VersionComparisonRequirement::class, $requirement);
        $this->assertSame('>= 1.0', $requirement->asString());
    }

    /**
     * @dataProvider comparisonProvider
     */
    public function testVersionRequirementCanBeCheckedUsingSimpleComparison(bool $expected, string $version, VersionComparisonRequirement $requirement): void
    {
        $this->assertSame($expected, $requirement->isSatisfiedBy($version));
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(InvalidVersionRequirementException::class);

        VersionRequirement::from('invalid');
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

    public function comparisonProvider(): array
    {
        return [
            [true, '1.0.0', new VersionComparisonRequirement('1.0.0', new VersionComparisonOperator('='))],
        ];
    }
}
