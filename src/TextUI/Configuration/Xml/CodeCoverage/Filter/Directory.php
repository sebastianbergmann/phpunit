<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Directory
{
    private string $path;
    private string $prefix;
    private string $suffix;
    private string $group;

    public function __construct(string $path, string $prefix, string $suffix, string $group)
    {
        $this->path   = $path;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->group  = $group;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function suffix(): string
    {
        return $this->suffix;
    }

    public function group(): string
    {
        return $this->group;
    }
}
