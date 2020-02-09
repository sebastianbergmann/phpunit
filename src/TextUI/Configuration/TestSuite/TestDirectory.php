<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Util\VersionComparisonOperator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class TestDirectory
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @var string
     */
    private $phpVersion;

    /**
     * @var VersionComparisonOperator
     */
    private $phpVersionOperator;

    public function __construct(string $path, string $prefix, string $suffix, string $phpVersion, VersionComparisonOperator $phpVersionOperator)
    {
        $this->path               = $path;
        $this->prefix             = $prefix;
        $this->suffix             = $suffix;
        $this->phpVersion         = $phpVersion;
        $this->phpVersionOperator = $phpVersionOperator;
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

    public function phpVersion(): string
    {
        return $this->phpVersion;
    }

    public function phpVersionOperator(): VersionComparisonOperator
    {
        return $this->phpVersionOperator;
    }
}
