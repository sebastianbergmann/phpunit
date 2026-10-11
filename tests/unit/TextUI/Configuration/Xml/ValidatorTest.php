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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Xml\Loader;

#[CoversClass(Validator::class)]
#[CoversClass(ValidationResult::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
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
        $this->assertSame(
            "\n" .
            "  Line 10:\n" .
            "  - Element 'phpunit', attribute 'forceCoversAnnotation': The attribute 'forceCoversAnnotation' is not allowed.\n" .
            "  - Element 'phpunit', attribute 'beStrictAboutCoversAnnotation': The attribute 'beStrictAboutCoversAnnotation' is not allowed.\n" .
            "  - Element 'phpunit', attribute 'beStrictAboutTodoAnnotatedTests': The attribute 'beStrictAboutTodoAnnotatedTests' is not allowed.\n" .
            "  - Element 'phpunit', attribute 'verbose': The attribute 'verbose' is not allowed.\n" .
            "\n" .
            "  Line 17:\n" .
            "  - Element 'filter': This element is not expected.\n",
            $result->asString(),
        );
    }
}
