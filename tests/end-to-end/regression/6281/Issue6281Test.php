<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6281;

use Exception;
use PHPUnit\Framework\TestCase;

final class Issue6281Test extends TestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('skip message');
    }

    protected function tearDown(): void
    {
        throw new Exception('exception message');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
