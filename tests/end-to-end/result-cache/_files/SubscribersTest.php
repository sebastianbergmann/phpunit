<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ResultCache;

use PHPUnit\Framework\TestCase;

final class SubscribersTest extends TestCase
{
    public function testIncomplete(): void
    {
        $this->markTestIncomplete('not yet implemented');
    }

    public function testSkipped(): void
    {
        $this->markTestSkipped('not applicable');
    }

    public function testRisky(): void
    {
    }
}
