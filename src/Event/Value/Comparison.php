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
final class Comparison
{
    private readonly string $expected;
    private readonly string $actual;

    public function __construct(string $expected, string $actual)
    {
        $this->expected = $expected;
        $this->actual   = $actual;
    }

    public function expected(): string
    {
        return $this->expected;
    }

    public function actual(): string
    {
        return $this->actual;
    }
}
