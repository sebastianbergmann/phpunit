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

    private ?string $versionRequirement;

    public function __construct(string $extension, ?string $requirement)
    {
        $this->extension          = $extension;
        $this->versionRequirement = $requirement;
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

    public function versionRequirement(): ?string
    {
        return $this->versionRequirement;
    }
}
