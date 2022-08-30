<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class Test
{
    private string $file;
    private TestStatus $status;
    private TestSize $size;

    public function __construct(string $file, TestStatus $status, TestSize $size)
    {
        $this->file   = $file;
        $this->status = $status;
        $this->size   = $size;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function status(): TestStatus
    {
        return $this->status;
    }

    public function size(): TestSize
    {
        return $this->size;
    }

    /**
     * @psalm-assert-if-true TestMethod $this
     */
    public function isTestMethod(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Phpt $this
     */
    public function isPhpt(): bool
    {
        return false;
    }

    abstract public function id(): string;

    abstract public function name(): string;
}
