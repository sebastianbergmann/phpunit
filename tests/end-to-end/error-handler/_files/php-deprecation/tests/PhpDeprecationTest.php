<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler\PhpDeprecation;

use function strlen;
use PHPUnit\Framework\TestCase;

final class PhpDeprecationTest extends TestCase
{
    public function testFromTestCode(): void
    {
        @strlen(null);

        $this->assertTrue(true);
    }

    public function testFromFirstParty(): void
    {
        $this->assertTrue((new FirstPartyClass)->method());
    }

    public function testFromThirdParty(): void
    {
        $this->assertTrue((new ThirdPartyClass)->method());
    }
}
