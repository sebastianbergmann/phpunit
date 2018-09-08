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

class Issue2085Test extends TestCase
{
    public function testShouldAbortSlowTestByEnforcingTimeLimit(): void
    {
        $this->assertTrue(true);
        \sleep(1.2);
        $this->assertTrue(true);
    }
}
