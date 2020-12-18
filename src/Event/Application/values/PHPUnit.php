<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Application;

use PHPUnit\Runner\Version;

final class PHPUnit
{
    public function version(): string
    {
        return Version::id();
    }

    public function series(): string
    {
        return Version::series();
    }
}
