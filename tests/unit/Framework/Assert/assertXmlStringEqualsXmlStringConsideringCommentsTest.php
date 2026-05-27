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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertXmlStringEqualsXmlStringConsideringComments')]
#[TestDox('assertXmlStringEqualsXmlStringConsideringComments()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertXmlStringEqualsXmlStringConsideringCommentsTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            ['<root><node/></root>', '<root><node/></root>'],
            ['<root><!-- comment --><node/></root>', '<root><!-- comment --><node/></root>'],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            ['<foo/>', '<bar/>'],
            ['<root><!-- comment --><node/></root>', '<root><node/></root>'],
            ['<root><!-- expected --><node/></root>', '<root><!-- actual --><node/></root>'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expectedXml, string $actualXml): void
    {
        $this->assertXmlStringEqualsXmlStringConsideringComments($expectedXml, $actualXml);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expectedXml, string $actualXml): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlStringConsideringComments($expectedXml, $actualXml);
    }
}
