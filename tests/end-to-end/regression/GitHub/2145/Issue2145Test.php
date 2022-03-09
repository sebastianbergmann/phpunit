<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use Exception;
use PHPUnit\Framework\TestCase;

class Issue2145Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        throw new Exception;
    }

    public function testOne(): void
    {
    }

    public function testTwo(): void
    {
    }
}
