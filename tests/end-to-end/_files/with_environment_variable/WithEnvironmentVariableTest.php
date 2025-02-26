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

use function getenv;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\TestCase;

#[WithEnvironmentVariable('FOO', 'foo')]
final class WithEnvironmentVariableTest extends TestCase
{
    public function testFOOShouldHaveValueFromClassAttribute1(): void
    {
        $this->assertSame('foo', $_ENV['FOO']);
        $this->assertSame('foo', getenv('FOO'));
    }

    #[WithEnvironmentVariable('FOO', 'bar')]
    public function testFOOShouldHaveValueOverriddenFromMethodAttribute(): void
    {
        $this->assertSame('bar', $_ENV['FOO']);
        $this->assertSame('bar', getenv('FOO'));
    }

    #[Depends('testFOOShouldHaveValueOverriddenFromMethodAttribute')]
    public function testFOOShouldHaveValueFromClassAttribute2(): void
    {
        $this->testFOOShouldHaveValueFromClassAttribute1();
    }

    #[WithEnvironmentVariable('FOO')]
    public function testFOOShouldHaveValueErasedFromMethodAttribute(): void
    {
        $this->assertFalse(isset($_ENV['FOO']));
        $this->assertFalse(getenv('FOO'));
    }

    #[Depends('testFOOShouldHaveValueErasedFromMethodAttribute')]
    public function testFOOShouldHaveValueFromClassAttribute3(): void
    {
        $this->testFOOShouldHaveValueFromClassAttribute1();
    }

    #[WithEnvironmentVariable('BAR', 'bar')]
    public function testBARShouldHaveValueFromMethodAttribute(): void
    {
        $this->assertSame('bar', $_ENV['BAR']);
        $this->assertSame('bar', getenv('BAR'));
    }

    #[Depends('testBARShouldHaveValueFromMethodAttribute')]
    public function testBARShouldHaveBeenRestoredToDefaultValue1(): void
    {
        $this->assertSame('2', $_ENV['BAR']);
        $this->assertSame('2', getenv('BAR'));
    }

    #[WithEnvironmentVariable('BAR')]
    public function testBARShouldHaveValueErasedFromMethodAttribute(): void
    {
        $this->assertFalse(isset($_ENV['BAR']));
        $this->assertFalse(getenv('BAR'));
    }

    #[Depends('testBARShouldHaveValueErasedFromMethodAttribute')]
    public function testBARShouldHaveBeenRestoredToDefaultValue2(): void
    {
        $this->testBARShouldHaveBeenRestoredToDefaultValue1();
    }

    #[WithEnvironmentVariable('BAZ', 'baz')]
    public function testBAZShouldHaveValueFromMethodAttribute(): void
    {
        $this->assertSame('baz', $_ENV['BAZ']);
        $this->assertSame('baz', getenv('BAZ'));
    }

    #[Depends('testBAZShouldHaveValueFromMethodAttribute')]
    public function testBAZShouldHaveBeenErased(): void
    {
        $this->assertFalse(isset($_ENV['BAZ']));
        $this->assertFalse(getenv('BAZ'));
    }

    #[RunInSeparateProcess]
    #[WithEnvironmentVariable('BAR', 'bar')]
    public function testRunInSeparateProcess(): void
    {
        $this->assertSame('foo', $_ENV['FOO']);
        $this->assertSame('foo', getenv('FOO'));

        $this->assertSame('bar', $_ENV['BAR']);
        $this->assertSame('bar', getenv('BAR'));
    }

    #[WithEnvironmentVariable('BAZ', '1')]
    #[WithEnvironmentVariable('BAZ', '2')]
    #[WithEnvironmentVariable('BAZ', '3')]
    public function testMultipleAttributesKeepTheLastValue(): void
    {
        $this->assertSame('3', $_ENV['BAZ']);
        $this->assertSame('3', getenv('BAZ'));
    }

    #[WithEnvironmentVariable('BAZ', '1')]
    #[RequiresEnvironmentVariable('BAZ', '1')]
    public function testUsingAlongWithRequiresEnvironmentVariableAttribute(): void
    {
        $this->assertSame('1', $_ENV['BAZ']);
        $this->assertSame('1', getenv('BAZ'));
    }
}
