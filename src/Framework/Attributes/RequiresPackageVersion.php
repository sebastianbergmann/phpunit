<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class RequiresPackageVersion
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
     * @param non-empty-string $packageName
     * @param non-empty-string $versionConstraint
     */
    public function __construct(string $packageName, string $versionConstraint)
    {
        $this->packageName       = $packageName;
        $this->versionConstraint = $versionConstraint;
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
