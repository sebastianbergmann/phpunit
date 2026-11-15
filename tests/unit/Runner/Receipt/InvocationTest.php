<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Invocation::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/receipt')]
final class InvocationTest extends TestCase
{
    public function testHasVersionOfPhpunit(): void
    {
        $this->assertSame('13.5.0', $this->invocation()->phpunitVersion());
    }

    public function testMayHavePhar(): void
    {
        $invocation = $this->invocation();

        $this->assertTrue($invocation->hasPhar());
        $this->assertSame('/path/to/phpunit.phar', $invocation->phar());
    }

    public function testMayNotHavePhar(): void
    {
        $this->assertFalse($this->invocationWithoutOptionalValues()->hasPhar());
    }

    public function testMayHaveChecksumOfPhar(): void
    {
        $invocation = $this->invocation();

        $this->assertTrue($invocation->hasPharChecksum());
        $this->assertSame('checksum-of-phar', $invocation->pharChecksum());
    }

    public function testMayNotHaveChecksumOfPhar(): void
    {
        $this->assertFalse($this->invocationWithoutOptionalValues()->hasPharChecksum());
    }

    public function testHasVersionOfPhp(): void
    {
        $this->assertSame('8.5.0', $this->invocation()->phpVersion());
    }

    public function testHasArguments(): void
    {
        $this->assertSame(['--compact', 'tests'], $this->invocation()->arguments());
    }

    public function testHasWorkingDirectory(): void
    {
        $this->assertSame('/path/to/project', $this->invocation()->workingDirectory());
    }

    public function testMayHaveConfigurationFile(): void
    {
        $invocation = $this->invocation();

        $this->assertTrue($invocation->hasConfigurationFile());
        $this->assertSame('/path/to/project/phpunit.xml', $invocation->configurationFile());
    }

    public function testMayNotHaveConfigurationFile(): void
    {
        $this->assertFalse($this->invocationWithoutOptionalValues()->hasConfigurationFile());
    }

    public function testMayHaveChecksumOfConfigurationFile(): void
    {
        $invocation = $this->invocation();

        $this->assertTrue($invocation->hasConfigurationFileChecksum());
        $this->assertSame('checksum-of-configuration-file', $invocation->configurationFileChecksum());
    }

    public function testMayNotHaveChecksumOfConfigurationFile(): void
    {
        $this->assertFalse($this->invocationWithoutOptionalValues()->hasConfigurationFileChecksum());
    }

    public function testHasBootstrapScripts(): void
    {
        $this->assertSame(['/path/to/project/tests/bootstrap.php'], $this->invocation()->bootstrapScripts());
    }

    public function testHasBootstrapClassesOfExtensions(): void
    {
        $this->assertSame(['Vendor\Extension\Bootstrap'], $this->invocation()->extensionClasses());
    }

    public function testHasPharsOfExtensions(): void
    {
        $this->assertSame(['/path/to/project/tools/extension.phar'], $this->invocation()->extensionPhars());
    }

    public function testHasStartTime(): void
    {
        $startTime = new DateTimeImmutable;

        $this->assertSame($startTime, $this->invocation($startTime)->startTime());
    }

    private function invocation(DateTimeImmutable $startTime = new DateTimeImmutable): Invocation
    {
        return new Invocation(
            '13.5.0',
            '/path/to/phpunit.phar',
            'checksum-of-phar',
            '8.5.0',
            ['--compact', 'tests'],
            '/path/to/project',
            '/path/to/project/phpunit.xml',
            'checksum-of-configuration-file',
            ['/path/to/project/tests/bootstrap.php'],
            ['Vendor\Extension\Bootstrap'],
            ['/path/to/project/tools/extension.phar'],
            $startTime,
        );
    }

    private function invocationWithoutOptionalValues(): Invocation
    {
        return new Invocation(
            '13.5.0',
            null,
            null,
            '8.5.0',
            [],
            '/path/to/project',
            null,
            null,
            [],
            [],
            [],
            new DateTimeImmutable,
        );
    }
}
