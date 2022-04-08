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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequiresPhpExtension
{
    private string $extension;
    private ?string $versionRequirement;

    public function __construct(string $extension, ?string $versionRequirement = null)
    {
        $this->extension          = $extension;
        $this->versionRequirement = $versionRequirement;
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
