<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function file_get_contents;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Xml\Loader;

#[CoversClass(Validator::class)]
#[CoversClass(ValidationResult::class)]
#[Small]
final class ValidatorTest extends TestCase
{
    public function testValidatesValidXmlFile(): void
    {
        $result = (new Validator)->validate(
            (new Loader)->loadFile(__DIR__ . '/../../../../../phpunit.xml'),
            (new SchemaFinder)->find(Version::series()),
        );

        $this->assertFalse($result->hasValidationErrors());
        $this->assertSame('', $result->asString());
    }

    public function testDoesNotValidateInvalidXmlFile(): void
    {
        $result = (new Validator)->validate(
            (new Loader)->loadFile(
                __DIR__ . '/../../../../end-to-end/migration/_files/possibility-to-migrate-from-92-is-detected/phpunit.xml',
            ),
            (new SchemaFinder)->find(Version::series()),
        );

        $this->assertTrue($result->hasValidationErrors());
        $this->assertStringEqualsStringIgnoringLineEndings(
            file_get_contents(__DIR__ . '/../../../../_files/invalid-configuration.txt'),
            $result->asString(),
        );
    }
}
