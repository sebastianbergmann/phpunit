<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class IncludePath extends \PHPUnit\Framework\TestCase
{
    public function testShouldSetIncludePath()
    {
        $this->assertContains('tests/TextUI', \ini_get('include_path'));
    }
}
