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
use PHPUnit\Util\Xml\XmlException;

#[CoversClass(SchemaDetectionResult::class)]
#[CoversClass(FailedSchemaDetectionResult::class)]
#[CoversClass(SuccessfulSchemaDetectionResult::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class SchemaDetectionResultTest extends TestCase
{
    public function testSuccessfulResultHasVersion(): void
    {
        $result = new SuccessfulSchemaDetectionResult('9.2');

        $this->assertTrue($result->detected());
        $this->assertSame('9.2', $result->version());
    }

    public function testFailedResultDoesNotHaveVersion(): void
    {
        $result = new FailedSchemaDetectionResult;

        $this->assertFalse($result->detected());

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('No supported schema was detected');

        $result->version();
    }
}
