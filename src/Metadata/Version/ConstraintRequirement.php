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

use PHPUnit\Metadata\InvalidVersionRequirementException;
use SebastianBergmann\VersionRequirement\ConstraintRequirement as ConstraintRequirementImplementation;
use SebastianBergmann\VersionRequirement\Exception as VersionRequirementException;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ConstraintRequirement extends Requirement
{
    /**
     * @var non-empty-string
     */
    private string $constraint;

    /**
     * @param non-empty-string $constraint
     *
     * @throws InvalidVersionRequirementException
     */
    public function __construct(string $constraint)
    {
        try {
            new ConstraintRequirementImplementation($constraint);
        } catch (VersionRequirementException) {
            throw new InvalidVersionRequirementException;
        }

        $this->constraint = $constraint;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return $this->constraint;
    }
}
