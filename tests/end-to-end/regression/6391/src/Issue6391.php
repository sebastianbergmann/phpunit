<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace TestFixture\Issue6391;

use Error;

class Issue6391
{
    public static $instance;

    public function __unserialize(array $data): void
    {
        throw new Error("Cannot unserialize '{$this}'");
    }
}
