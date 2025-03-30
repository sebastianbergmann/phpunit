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
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\TestCase;

#[WithEnvironmentVariable('FOO', 'foo')]
final class WithEnvironmentVariableHookTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::assertEnvironmentVariablesHaveDefaultValues();
    }

    public static function tearDownAfterClass(): void
    {
        self::assertEnvironmentVariablesHaveDefaultValues();
    }

    protected function setUp(): void
    {
        $this->assertEnvironmentVariablesHaveCustomValues();
    }

    protected function tearDown(): void
    {
        $this->assertEnvironmentVariablesHaveCustomValues();
    }

    #[WithEnvironmentVariable('BAR', 'bar')]
    public function testFOOShouldHaveValueErasedFromMethodAttribute(): void
    {
        $this->assertEnvironmentVariablesHaveCustomValues();
    }

    private function assertEnvironmentVariablesHaveCustomValues(): void
    {
        $this->assertSame('foo', $_ENV['FOO']);
        $this->assertSame('foo', getenv('FOO'));

        $this->assertSame('bar', $_ENV['BAR']);
        $this->assertSame('bar', getenv('BAR'));
    }

    private static function assertEnvironmentVariablesHaveDefaultValues(): void
    {
        self::assertSame('1', $_ENV['FOO']);
        self::assertSame('1', getenv('FOO'));

        self::assertSame('2', $_ENV['BAR']);
        self::assertSame('2', getenv('BAR'));
    }
}
