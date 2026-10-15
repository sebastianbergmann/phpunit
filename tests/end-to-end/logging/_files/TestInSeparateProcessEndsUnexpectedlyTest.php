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

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class TestInSeparateProcessEndsUnexpectedlyTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        exit(0);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
