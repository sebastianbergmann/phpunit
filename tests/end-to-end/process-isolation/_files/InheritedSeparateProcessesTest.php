<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5838;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
abstract class InheritedSeparateProcessesBaseTest extends TestCase
{
}

final class InheritedSeparateProcessesTest extends InheritedSeparateProcessesBaseTest
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
