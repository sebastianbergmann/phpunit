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
final class Group extends Metadata
{
    private string $groupName;

    public function __construct(string $groupName)
    {
        $this->groupName = $groupName;
    }

    public function isGroup(): bool
    {
        return true;
    }

    public function groupName(): string
    {
        return $this->groupName;
    }
}
