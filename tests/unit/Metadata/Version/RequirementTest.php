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
    /**
     * @return non-empty-list<array{bool, string, ConstraintRequirement}>
     */
    public static function constraintProvider(): array
    {
        return [
            [true, '1.0.0', new ConstraintRequirement('1.0.0')],
            [false, '1.0.1', new ConstraintRequirement('1.0.0')],
            [true, '8.5.0-dev', new ConstraintRequirement('^8.5')],
            [false, '9.0.0', new ConstraintRequirement('^8.5')],
        ];
    }

    /**
     * @return non-empty-list<array{bool, string, ComparisonRequirement}>
     */
    public static function comparisonProvider(): array
    {
        return [
            [true, '1.0.0', new ComparisonRequirement('1.0.0', new VersionComparisonOperator('='))],
            [false, '1.0.1', new ComparisonRequirement('1.0.0', new VersionComparisonOperator('='))],
            [true, '7.4.33', new ComparisonRequirement('8.0.0', new VersionComparisonOperator('<'))],
            [false, '8.0.0', new ComparisonRequirement('8.0.0', new VersionComparisonOperator('<'))],
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

    public function testVersionConstraintIsComplete(): void
    {
        $this->assertTrue(new ConstraintRequirement('^8.5')->isComplete());
    }

    public function testCannotBeCreatedFromInvalidVersionConstraint(): void
    {
        $this->expectException(InvalidVersionRequirementException::class);

        new ConstraintRequirement('^8.*');
    }

    public function testCanBeCreatedFromStringWithSimpleComparison(): void
    {
        $requirement = Requirement::from('>= 1.0');

        $this->assertInstanceOf(ComparisonRequirement::class, $requirement);
        $this->assertSame('>= 1.0', $requirement->asString());
        $this->assertSame('1.0', $requirement->version());
    }

    public function testCannotBeCreatedFromStringWithSimpleComparisonWithoutOperator(): void
    {
        $this->expectException(InvalidVersionRequirementException::class);

        Requirement::from('8.5.0RC1');
    }

    #[DataProvider('comparisonProvider')]
    public function testVersionRequirementCanBeCheckedUsingSimpleComparison(bool $expected, string $version, ComparisonRequirement $requirement): void
    {
        $this->assertSame($expected, $requirement->isSatisfiedBy($version));
    }

    public function testSimpleComparisonWithMajorMinorAndPatchLevelIsComplete(): void
    {
        $this->assertTrue(new ComparisonRequirement('8.5.0', new VersionComparisonOperator('<='))->isComplete());
    }

    public function testSimpleComparisonWithoutPatchLevelIsIncomplete(): void
    {
        $this->assertFalse(new ComparisonRequirement('8.5', new VersionComparisonOperator('<='))->isComplete());
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(InvalidVersionRequirementException::class);

        Requirement::from('invalid');
    }
}
