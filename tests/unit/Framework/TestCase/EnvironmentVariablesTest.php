<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use function getenv;
use function putenv;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BackedUpEnvironmentVariable;
use PHPUnit\TestFixture\Metadata\Attribute\WithEnvironmentVariableTest;
use PHPUnit\TestFixture\TestWithDifferentNames;

#[CoversClass(EnvironmentVariables::class)]
#[CoversClass(BackedUpEnvironmentVariable::class)]
#[Small]
final class EnvironmentVariablesTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('foo=original');
        putenv('bar');

        $_ENV['foo'] = 'original';

        unset($_ENV['bar']);
    }

    protected function tearDown(): void
    {
        putenv('foo');
        putenv('bar');

        unset($_ENV['foo'], $_ENV['bar']);
    }

    public function testSetsEnvironmentVariablesConfiguredForTestAndRestoresThemAfterwards(): void
    {
        $environmentVariables = new EnvironmentVariables;

        $environmentVariables->set(WithEnvironmentVariableTest::class, 'testOne');

        $this->assertFalse(getenv('foo'));
        $this->assertArrayNotHasKey('foo', $_ENV);
        $this->assertSame('baz', getenv('bar'));
        $this->assertSame('baz', $_ENV['bar']);

        $environmentVariables->restore();

        $this->assertSame('original', getenv('foo'));
        $this->assertSame('original', $_ENV['foo']);
        $this->assertFalse(getenv('bar'));
        $this->assertArrayNotHasKey('bar', $_ENV);
    }

    public function testDoesNothingForTestThatDoesNotConfigureEnvironmentVariables(): void
    {
        $environmentVariables = new EnvironmentVariables;

        $environmentVariables->set(TestWithDifferentNames::class, 'testWithName');

        $this->assertSame('original', getenv('foo'));
        $this->assertSame('original', $_ENV['foo']);

        $environmentVariables->restore();

        $this->assertSame('original', getenv('foo'));
        $this->assertSame('original', $_ENV['foo']);
    }
}
