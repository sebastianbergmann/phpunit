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

use const STDERR;
use function fwrite;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class TestInSeparateProcessWritesToStderrTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        fwrite(STDERR, 'message written to STDERR');

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
