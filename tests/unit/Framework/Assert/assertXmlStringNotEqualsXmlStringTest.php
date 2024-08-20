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
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertXmlStringNotEqualsXmlString')]
#[TestDox('assertXmlStringNotEqualsXmlString()')]
#[Small]
final class assertXmlStringNotEqualsXmlStringTest extends TestCase
{
    #[DataProviderExternal(assertXmlStringEqualsXmlStringTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expectedXml, string $actualXml): void
    {
        $this->assertXmlStringNotEqualsXmlString($expectedXml, $actualXml);
    }

    #[DataProviderExternal(assertXmlStringEqualsXmlStringTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expectedXml, string $actualXml): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlString($expectedXml, $actualXml);
    }
}
