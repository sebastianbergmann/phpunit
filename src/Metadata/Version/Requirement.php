<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Version;

use function assert;
use function explode;
use PHPUnit\Metadata\InvalidVersionRequirementException;
use PHPUnit\Util\VersionComparisonOperator;
use SebastianBergmann\VersionRequirement\ComparisonRequirement as ComparisonRequirementImplementation;
use SebastianBergmann\VersionRequirement\ConstraintRequirement as ConstraintRequirementImplementation;
use SebastianBergmann\VersionRequirement\Exception;
use SebastianBergmann\VersionRequirement\Requirement as RequirementImplementation;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Requirement
{
    /**
     * @param non-empty-string $versionRequirement
     *
     * @throws InvalidVersionRequirementException
     */
    public static function from(string $versionRequirement): self
    {
        try {
            $requirement = RequirementImplementation::from($versionRequirement);
        } catch (Exception) {
            throw new InvalidVersionRequirementException;
        }

        if ($requirement instanceof ComparisonRequirementImplementation) {
            return new ComparisonRequirement(
                $requirement->version(),
                new VersionComparisonOperator(
                    explode(' ', $requirement->asString(), 2)[0],
                ),
            );
        }

        assert($requirement instanceof ConstraintRequirementImplementation);

        return new ConstraintRequirement($requirement->asString());
    }

    abstract public function asString(): string;
}
