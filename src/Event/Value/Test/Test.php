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

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class Test
{
    private readonly string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function file(): string
    {
        return $this->file;
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
