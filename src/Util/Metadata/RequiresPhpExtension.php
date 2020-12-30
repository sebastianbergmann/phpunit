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

use PHPUnit\Util\VersionComparisonOperator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class RequiresPhpExtension extends Metadata
{
    private string $extension;

    private ?string $version;

    private ?VersionComparisonOperator $operator = null;

    public function __construct(string $extension, ?string $version, VersionComparisonOperator $operator)
    {
        $this->extension = $extension;
        $this->version   = $version;
        $this->operator  = $operator;
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
        return $this->version !== null;
    }

    public function version(): ?string
    {
        return $this->version;
    }

    public function operator(): ?VersionComparisonOperator
    {
        return $this->operator;
    }
}
