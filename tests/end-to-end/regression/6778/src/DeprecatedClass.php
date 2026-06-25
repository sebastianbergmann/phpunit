<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6778;

use const E_USER_DEPRECATED;
use function trigger_error;

@trigger_error('triggered while autoloading a class during requirement checking', E_USER_DEPRECATED);

final class DeprecatedClass
{
    public function foo(): void
    {
    }
}
