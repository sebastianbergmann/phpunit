<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6599;

use PHPUnit\Framework\TestCase;

final class Issue6599FailedSetUpTest extends TestCase
{
    protected function setUp(): void
    {
        $this->assertTrue(false, 'assertion failure in setUp');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
