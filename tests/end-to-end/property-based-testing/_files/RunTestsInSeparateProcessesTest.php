<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class RunTestsInSeparateProcessesTest extends TestCase
{
    #[Property(examples: 10)]
    public function testIsDeclaredInAClassThatRunsTestsInSeparateProcesses(int $value): void
    {
        $this->assertTrue(true);
    }
}
