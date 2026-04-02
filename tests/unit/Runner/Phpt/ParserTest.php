<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use function glob;
use function realpath;
use function str_replace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parser::class)]
#[CoversClass(PhptExternalFileCannotBeLoadedException::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/phpt')]
final class ParserTest extends TestCase
{
    /**
     * @return non-empty-array<non-empty-string, array{0: non-empty-string}>
     */
    public static function unsupportedSections(): array
    {
        $data = [];

        foreach (glob(__DIR__ . '/../../../_files/phpt/unsupported/*.phpt') as $file) {
            $data[str_replace([__DIR__ . '/../../../_files/phpt/unsupported/', '.phpt'], '', $file)] = [$file];
        }

        return $data;
    }

    /**
     * @return non-empty-list<array{0: non-empty-string}>
     */
    public static function invalidFiles(): array
    {
        $data = [];

        foreach (glob(__DIR__ . '/../../../_files/phpt/invalid/*.phpt') as $file) {
            $data[] = [$file];
        }

        return $data;
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('unsupportedSections')]
    #[TestDox('PHPT section --$_dataName-- is not supported')]
    public function testRejectsUnsupportedSections(string $file): void
    {
        $parser = new Parser;

        $this->expectException(UnsupportedPhptSectionException::class);

        $parser->parse($file);
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('invalidFiles')]
    public function testRejectsInvalidPhptFile(string $file): void
    {
        $parser = new Parser;

        $this->expectException(InvalidPhptFileException::class);

        $parser->parse($file);
    }

    public function testParsesFileeofSection(): void
    {
        $parser   = new Parser;
        $sections = $parser->parse(__DIR__ . '/../../../_files/phpt/fileeof.phpt');

        $this->assertArrayHasKey('FILE', $sections);
        $this->assertArrayNotHasKey('FILEEOF', $sections);
        $this->assertStringContainsString('echo "hello"', $sections['FILE']);
    }

    public function testRejectsExternalFileThatDoesNotExist(): void
    {
        $parser = new Parser;

        $this->expectException(PhptExternalFileCannotBeLoadedException::class);

        $parser->parse(__DIR__ . '/../../../_files/phpt/external-missing-file.phpt');
    }

    #[TestDox('parseIniSection() skips settings without equals sign')]
    public function testParseIniSectionSkipsSettingsWithoutEqualsSign(): void
    {
        $parser = new Parser;
        $result = $parser->parseIniSection("foo=bar\nno_equals_here\nbaz=qux");

        $this->assertSame('bar', $result['foo']);
        $this->assertSame('qux', $result['baz']);
        $this->assertArrayNotHasKey('no_equals_here', $result);
    }

    #[TestDox('parseIniSection() accumulates extension values as array')]
    public function testParseIniSectionHandlesExtensionAsArray(): void
    {
        $parser = new Parser;
        $result = $parser->parseIniSection("extension=one.so\nextension=two.so");

        $this->assertSame(['one.so', 'two.so'], $result['extension']);
    }

    #[TestDox('parseIniSection() accumulates zend_extension values as array')]
    public function testParseIniSectionHandlesZendExtensionAsArray(): void
    {
        $parser = new Parser;
        $result = $parser->parseIniSection("zend_extension=opcache.so\nzend_extension=xdebug.so");

        $this->assertSame(['opcache.so', 'xdebug.so'], $result['zend_extension']);
    }

    #[TestDox('FILE_EXTERNAL sets FILE_EXTERNAL_PATH to resolved path of external file')]
    public function testFileExternalPathIsSet(): void
    {
        $parser   = new Parser;
        $file     = __DIR__ . '/../../../_files/phpt/file-external/test.phpt';
        $sections = $parser->parse($file);

        $this->assertArrayHasKey('FILE_EXTERNAL_PATH', $sections);
        $this->assertSame(
            realpath(__DIR__ . '/../../../_files/phpt/file-external/external-code.php'),
            $sections['FILE_EXTERNAL_PATH'],
        );
    }
}
