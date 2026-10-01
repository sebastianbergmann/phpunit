<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelTimeout;

use function sleep;
use PHPUnit\Framework\TestCase;

final class ASlowTest extends TestCase
{
    public function testFast(): void
    {
        $this->assertTrue(true);
    }

    public function testSlow(): void
    {
        sleep(10);

        $this->assertTrue(true);
    }

    public function testNeverRuns(): void
    {
        $this->assertTrue(true);
    }
}
