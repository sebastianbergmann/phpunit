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

/**
 * @runClassInSeparateProcess
 */
class Issue3167ClassTest extends TestCase
{
    public function testSTDOUT(): void
    {
        $fp = \fopen('php://stdout', 'w');
        \fclose($fp);
        $this->assertTrue(false);
    }
}
