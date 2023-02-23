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
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class RequiresOperatingSystem extends Metadata
{
    private readonly string $operatingSystem;

    public function __construct(int $level, string $operatingSystem)
    {
        parent::__construct($level);

        $this->operatingSystem = $operatingSystem;
    }

    public function isRequiresOperatingSystem(): bool
    {
        return true;
    }

    public function operatingSystem(): string
    {
        return $this->operatingSystem;
    }
}
