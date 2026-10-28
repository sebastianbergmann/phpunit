<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\TestCase;

final class MarkedIncompleteInTearDownTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testSucceeds(): void
    {
        $this->assertTrue(true);
    }

    public function testFails(): void
    {
        $this->assertTrue(false);
    }
}
