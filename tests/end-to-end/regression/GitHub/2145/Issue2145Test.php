<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Issue2145Test extends PHPUnit\Framework\TestCase
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
