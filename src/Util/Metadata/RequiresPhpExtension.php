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
final class RequiresPhpExtension extends Metadata
{
    private string $extension;

    private ?VersionRequirement $versionRequirement;

    public function __construct(string $extension, ?VersionRequirement $versionRequirement)
    {
        $this->extension          = $extension;
        $this->versionRequirement = $versionRequirement;
    }

    public function isRequiresPhpExtension(): bool
    {
        return true;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function hasVersionRequirement(): bool
    {
        return $this->versionRequirement !== null;
    }

    public function versionRequirement(): ?VersionRequirement
    {
        return $this->versionRequirement;
    }
}
