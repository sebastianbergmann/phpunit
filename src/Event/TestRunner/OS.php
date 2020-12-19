<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use const PHP_OS;
use const PHP_OS_FAMILY;

final class OS
{
    public function asString(): string
    {
        return PHP_OS;
    }

    public function family(): string
    {
        return PHP_OS_FAMILY;
    }
}
