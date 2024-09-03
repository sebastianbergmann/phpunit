<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5884;

use const E_USER_DEPRECATED;
use function chmod;
use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function trigger_error;
use function unlink;
use Exception;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

final class FooTest extends TestCase
{
    #[RequiresPhpunit('^11.0')]
    public function testExpectUserDeprecationMessageNOTIgnoringDeprecations(): void
    {
        $this->expectUserDeprecationMessage('foo');

        trigger_error('foo', E_USER_DEPRECATED);
    }

    #[RequiresPhpunit('^11.0')]
    #[IgnoreDeprecations]
    public function testExpectUserDeprecationMessageANDIgnoringDeprecations(): void
    {
        $this->expectUserDeprecationMessage('foo');

        trigger_error('foo', E_USER_DEPRECATED);
    }

    public function testPcreHasUtf8Support(): void
    {
        $this->assertIsBool(Foo::pcreHasUtf8Support());
    }

    public function testStreamToNonWritableFileWithPHPUnitErrorHandler(): void
    {
        // Create an unwritable file.
        $filename = tempnam(sys_get_temp_dir(), 'RLT');

        if (file_put_contents($filename, 'foo')) {
            chmod($filename, 0o444);
        }

        try {
            Foo::openFile($filename);
        } catch (Exception $e) {
            // This "Failed to open stream" exception is expected.
        }

        // Now verify the original file is unchanged.
        $contents = file_get_contents($filename);
        $this->assertSame('foo', $contents);

        chmod($filename, 0o755);
        unlink($filename);
    }

    #[WithoutErrorHandler]
    public function testStreamToNonWritableFileWithoutPHPUnitErrorHandler(): void
    {
        // Create an unwritable file.
        $filename = tempnam(sys_get_temp_dir(), 'RLT');

        if (file_put_contents($filename, 'foo')) {
            chmod($filename, 0o444);
        }

        try {
            Foo::openFile($filename);
        } catch (Exception $e) {
            // This "Failed to open stream" exception is expected.
        }

        // Now verify the original file is unchanged.
        $contents = file_get_contents($filename);
        $this->assertSame('foo', $contents);

        chmod($filename, 0o755);
        unlink($filename);
    }

    public function testStreamToInvalidFile(): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'RLT') . '/missing/directory';

        $this->expectException(Exception::class);
        // First character (F) can be upper or lowercase depending on PHP version.
        $this->expectExceptionMessage('ailed to open stream');

        Foo::openFile($filename);
    }
}
