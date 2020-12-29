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

    private ?string $version;

    /**
     * @psalm-var '<'|'lt'|'<='|'le'|'>'|'gt'|'>='|'ge'|'=='|'='|'eq'|'!='|'<>'|'ne'
     */
    private string $operator;

    /**
     * @psalm-param '<'|'lt'|'<='|'le'|'>'|'gt'|'>='|'ge'|'=='|'='|'eq'|'!='|'<>'|'ne' $operator
     */
    public function __construct(string $extension, ?string $version, string $operator)
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

    public function operator(): string
    {
        return $this->operator;
    }
}
