<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Runtime;

use const PHP_EXTRA_VERSION;
use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_RELEASE_VERSION;
use const PHP_SAPI;
use const PHP_VERSION;
use const PHP_VERSION_ID;

/**
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class PHP
{
    public function asString(): string
    {
        return PHP_VERSION;
    }

    public function sapi(): string
    {
        return PHP_SAPI;
    }

    public function major(): int
    {
        return PHP_MAJOR_VERSION;
    }

    public function minor(): int
    {
        return PHP_MINOR_VERSION;
    }

    public function patch(): int
    {
        return PHP_RELEASE_VERSION;
    }

    public function extra(): string
    {
        return PHP_EXTRA_VERSION;
    }

    public function id(): int
    {
        return PHP_VERSION_ID;
    }

    public function extensions(): Extensions
    {
        return new Extensions;
    }
}
