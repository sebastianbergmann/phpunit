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

class IniTest extends TestCase
{
    public function testIni(): void
    {
        $this->assertEquals('application/x-test', \ini_get('default_mimetype'));
    }
}
