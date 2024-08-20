<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertXmlStringEqualsXmlString')]
#[TestDox('assertXmlStringEqualsXmlString()')]
#[Small]
final class assertXmlStringEqualsXmlStringTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            ['<root/>', '<root/>'],
            ['<root/>', '<root></root>'],
            [
                <<<'XML'
<?xml version="1.0"?>
<root>
    <node />
</root>
XML,
                <<<'XML'
<?xml version="1.0"?>
<root>
<node />
</root>
XML
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            ['<foo/>', '<bar/>'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expectedXml, string $actualXml): void
    {
        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expectedXml, string $actualXml): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }
}
