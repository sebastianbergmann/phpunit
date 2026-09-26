<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelStopOnClean;

use PHPUnit\Framework\TestCase;

final class FailingTest extends TestCase
{
    public function testThatFails(): void
    {
        $this->assertTrue(false);
    }
}
