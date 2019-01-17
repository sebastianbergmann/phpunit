<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class IgnoreCodeCoverageClassTest extends TestCase
{
    public function testReturnTrue(): void
    {
        $sut = new IgnoreCodeCoverageClass;
        $this->assertTrue($sut->returnTrue());
    }

    public function testReturnFalse(): void
    {
        $sut = new IgnoreCodeCoverageClass;
        $this->assertFalse($sut->returnFalse());
    }
}
