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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class TestFile
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $phpVersion;

    /**
     * @var string
     */
    private $phpVersionOperator;

    public function __construct(string $path, string $phpVersion, $phpVersionOperator)
    {
        $this->path               = $path;
        $this->phpVersion         = $phpVersion;
        $this->phpVersionOperator = $phpVersionOperator;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function phpVersion(): string
    {
        return $this->phpVersion;
    }

    public function phpVersionOperator(): string
    {
        return $this->phpVersionOperator;
    }
}
