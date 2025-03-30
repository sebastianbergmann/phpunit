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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Version\ComparisonRequirement;
use PHPUnit\Metadata\Version\ConstraintRequirement;
use PHPUnit\Metadata\Version\Requirement;
use PHPUnit\Util\VersionComparisonOperator;

#[CoversClass(ComparisonRequirement::class)]
#[CoversClass(ConstraintRequirement::class)]
#[CoversClass(Requirement::class)]
#[UsesClass(VersionComparisonOperator::class)]
#[Small]
#[Group('metadata')]
final class RequirementTest extends TestCase
{
    public static function constraintProvider(): array
    {
        return [
            [
                true,
                '1.0.0',
                new ConstraintRequirement(
                    (new VersionConstraintParser)->parse('1.0.0'),
                ),
            ],
        ];
    }

    public static function comparisonProvider(): array
    {
        return [
            [true, '1.0.0', new ComparisonRequirement('1.0.0', new VersionComparisonOperator('='))],
        ];
    }

    public function testCanBeCreatedFromStringWithVersionConstraint(): void
    {
        $requirement = Requirement::from('^1.0');

        $this->assertInstanceOf(ConstraintRequirement::class, $requirement);
        $this->assertSame('^1.0', $requirement->asString());
    }

    #[DataProvider('constraintProvider')]
    public function testVersionRequirementCanBeCheckedUsingVersionConstraint(bool $expected, string $version, ConstraintRequirement $requirement): void
    {
        $this->assertSame($expected, $requirement->isSatisfiedBy($version));
    }

    public function testCanBeCreatedFromStringWithSimpleComparison(): void
    {
        $requirement = Requirement::from('>= 1.0');

        $this->assertInstanceOf(ComparisonRequirement::class, $requirement);
        $this->assertSame('>= 1.0', $requirement->asString());
    }

    #[DataProvider('comparisonProvider')]
    public function testVersionRequirementCanBeCheckedUsingSimpleComparison(bool $expected, string $version, ComparisonRequirement $requirement): void
    {
        $this->assertSame($expected, $requirement->isSatisfiedBy($version));
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(InvalidVersionRequirementException::class);

        Requirement::from('invalid');
    }
}
