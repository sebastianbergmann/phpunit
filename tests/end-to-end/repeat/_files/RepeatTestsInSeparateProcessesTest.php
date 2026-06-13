<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Repeat;

use PHPUnit\Framework\Attributes\Repeat;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class RepeatTestsInSeparateProcessesTest extends TestCase
{
    #[Repeat(2)]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
