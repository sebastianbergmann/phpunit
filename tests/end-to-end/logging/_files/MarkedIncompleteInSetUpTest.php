<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Logging;

use PHPUnit\Framework\TestCase;

final class MarkedIncompleteInSetUpTest extends TestCase
{
    protected function setUp(): void
    {
        if ($this->name() === 'testOne') {
            $this->markTestIncomplete('message');
        }
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
