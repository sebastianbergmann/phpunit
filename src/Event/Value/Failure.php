<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use Throwable;

/**
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Failure
{
    private string $testName;
    private Throwable $throwable;

    public function __construct(string $testName, Throwable $throwable)
    {
        $this->testName  = $testName;
        $this->throwable = $throwable;
    }

    public function testName(): string
    {
        return $this->testName;
    }

    public function throwable(): Throwable
    {
        return $this->throwable;
    }
}
