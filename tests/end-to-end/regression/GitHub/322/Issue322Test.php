<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue322Test extends TestCase
{
    /**
     * @group one
     */
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @group two
     */
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
