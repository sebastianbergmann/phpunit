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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class RequiresPhp extends Metadata
{
    private VersionRequirement $versionRequirement;

    public function __construct(VersionRequirement $versionRequirement)
    {
        $this->versionRequirement = $versionRequirement;
    }

    public function isRequiresPhp(): bool
    {
        return true;
    }

    public function versionRequirement(): VersionRequirement
    {
        return $this->versionRequirement;
    }
}
