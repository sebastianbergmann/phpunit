<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Xml;

use const PHP_EOL;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

/**
 * @small
 *
 * @covers \PHPUnit\Util\Xml\ValidationResult
 * @covers \PHPUnit\Util\Xml\Validator
 */
final class ValidatorTest extends TestCase
{
    public function testValidatesValidXmlFile(): void
    {
        $result = (new Validator())->validate(
            (new Loader)->loadFile(
                __DIR__ . '/../../../../phpunit.xml',
                false,
                true,
                true
            ),
            (new SchemaFinder)->find(Version::series())
        );

        $this->assertFalse($result->hasValidationErrors());
        $this->assertSame('', $result->asString());
    }

    public function testDoesNotValidateInvalidXmlFile(): void
    {
        $result = (new Validator())->validate(
            (new Loader)->loadFile(
                __DIR__ . '/../../../end-to-end/migration/possibility-to-migrate-from-92-is-detected/phpunit.xml',
                false,
                true,
                true
            ),
            (new SchemaFinder)->find(Version::series())
        );

        $this->assertTrue($result->hasValidationErrors());
        $this->assertSame(PHP_EOL . '  Line 17:' . PHP_EOL . '  - Element \'filter\': This element is not expected.' . PHP_EOL, $result->asString());
    }
}
