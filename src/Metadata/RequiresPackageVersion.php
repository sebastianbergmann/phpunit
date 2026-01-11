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

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RequiresPackageVersion extends Metadata
{
    /**
     * @var non-empty-string
     */
    private string $packageName;

    /**
     * @var non-empty-string
     */
    private string $versionConstraint;

    /**
     * @param int<0, 1>        $level
     * @param non-empty-string $packageName
     * @param non-empty-string $versionConstraint
     */
    protected function __construct(int $level, string $packageName, string $versionConstraint)
    {
        parent::__construct($level);

        $this->packageName       = $packageName;
        $this->versionConstraint = $versionConstraint;
    }

    public function isRequiresPackageVersion(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function packageName(): string
    {
        return $this->packageName;
    }

    /**
     * @return non-empty-string
     */
    public function versionConstraint(): string
    {
        return $this->versionConstraint;
    }
}
