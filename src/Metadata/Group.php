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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class Group extends Metadata
{
    private readonly string $groupName;

    protected function __construct(int $level, string $groupName)
    {
        parent::__construct($level);

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
