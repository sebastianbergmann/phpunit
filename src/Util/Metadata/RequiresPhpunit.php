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
final class RequiresPhpunit extends Metadata
{
    private string $versionRequirement;

    public function __construct(string $versionRequirement)
    {
        $this->versionRequirement = $versionRequirement;
    }

    public function isRequiresPhpunit(): bool
    {
        return true;
    }

    public function versionRequirement(): string
    {
        return $this->versionRequirement;
    }
}
