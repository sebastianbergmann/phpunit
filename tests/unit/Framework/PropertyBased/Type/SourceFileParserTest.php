<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use const PHP_OS_FAMILY;
use function chmod;
use function file_put_contents;
use function octdec;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceFileParser::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class SourceFileParserTest extends TestCase
{
    public function testParsesAFile(): void
    {
        $this->assertNotEmpty(SourceFileParser::parse(__DIR__ . '/../../../../_files/PropertyBased/NameResolution/ClassWithImports.php'));
    }

    public function testDoesNotParseTheFileThatWasParsedLastAgain(): void
    {
        $file = __DIR__ . '/../../../../_files/PropertyBased/NameResolution/ClassWithImports.php';

        $this->assertSame(SourceFileParser::parse($file), SourceFileParser::parse($file));
    }

    public function testReturnsNullForAFileThatCannotBeRead(): void
    {
        $this->assertNull(SourceFileParser::parse(__DIR__ . '/does-not-exist.php'));
    }

    public function testReturnsNullForAFileThatExistsButCannotBeRead(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $file = tempnam(sys_get_temp_dir(), 'phpunit');

        file_put_contents($file, '<?php final class Example {}');
        chmod($file, octdec('0'));

        try {
            $this->assertNull(SourceFileParser::parse($file));
        } finally {
            chmod($file, octdec('600'));
            unlink($file);
        }
    }

    public function testReturnsNullForAFileThatCannotBeParsed(): void
    {
        $this->assertNull(SourceFileParser::parse(__DIR__ . '/../../../../_files/PropertyBased/NameResolution/CannotBeParsed.php'));
    }
}
