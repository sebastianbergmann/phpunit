<?php
declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

class Issue3364SetupTest extends TestCase
{
    public function setUp(): void
    {
        throw new \RuntimeException('throw exception in setUp');
    }

    public function testOneWithSetupException(): void
    {
        $this->fail();
    }

    public function testTwoWithSetupException(): void
    {
        $this->fail();
    }
}
