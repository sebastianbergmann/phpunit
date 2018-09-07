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

class Issue1471Test extends TestCase
{
    public function testFailure(): void
    {
        $this->expectOutputString('*');

        print '*';

        $this->assertTrue(false);
    }
}
