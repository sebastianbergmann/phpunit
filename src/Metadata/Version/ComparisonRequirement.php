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

use PHPUnit\Util\VersionComparisonOperator;
use SebastianBergmann\VersionRequirement\ComparisonRequirement as ComparisonRequirementImplementation;
use SebastianBergmann\VersionRequirement\VersionComparisonOperator as VersionComparisonOperatorImplementation;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ComparisonRequirement extends Requirement
{
    /**
     * @var non-empty-string
     */
    private string $version;
    private VersionComparisonOperator $operator;

    /**
     * @param non-empty-string $version
     */
    public function __construct(string $version, VersionComparisonOperator $operator)
    {
        $this->version  = $version;
        $this->operator = $operator;
    }

    public function isSatisfiedBy(string $version): bool
    {
        return $this->implementation()->isSatisfiedBy($version);
    }

    public function asString(): string
    {
        return $this->operator->asString() . ' ' . $this->version;
    }

    /**
     * @return non-empty-string
     */
    public function version(): string
    {
        return $this->version;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function isComplete(): bool
    {
        return $this->implementation()->isComplete();
    }

    private function implementation(): ComparisonRequirementImplementation
    {
        return new ComparisonRequirementImplementation(
            $this->version,
            new VersionComparisonOperatorImplementation($this->operator->asString()),
        );
    }
}
