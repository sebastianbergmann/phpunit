<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\requires_environment_variable;

use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    #[RequiresEnvironmentVariable('FOO', 'bar')]
    public function testShouldNotRunFOOHasWrongValue(): void
    {
        $this->fail();
    }

    #[RequiresEnvironmentVariable('BAR')]
    public function testShouldNotRunBARIsEmpty(): void
    {
        $this->fail();
    }

    #[RequiresEnvironmentVariable('BAZ')]
    public function testShouldNotRunBAZDoesNotExist(): void
    {
        $this->fail();
    }

    #[RequiresEnvironmentVariable('FOO')]
    public function testOneShouldRun(): void
    {
        $this->assertTrue(true);
    }

    #[RequiresEnvironmentVariable('FOO', '1')]
    public function testTwoShouldRun(): void
    {
        $this->assertTrue(true);
    }

    #[RequiresEnvironmentVariable('BAR', '')]
    public function testThreeShouldRun(): void
    {
        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    #[RequiresEnvironmentVariable('FOO', '1')]
    public function testMustRunInSeparateProcess(): void
    {
        $this->assertTrue(true);
    }
}
