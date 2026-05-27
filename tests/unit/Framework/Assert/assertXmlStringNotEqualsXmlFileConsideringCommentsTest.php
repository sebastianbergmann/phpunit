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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertXmlStringNotEqualsXmlFileConsideringComments')]
#[TestDox('assertXmlStringNotEqualsXmlFileConsideringComments()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertXmlStringNotEqualsXmlFileConsideringCommentsTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertXmlStringNotEqualsXmlFileConsideringComments(
            TEST_FILES_PATH . 'xml-with-comments.xml',
            '<root><node/></root>',
        );
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlFileConsideringComments(
            TEST_FILES_PATH . 'xml-with-comments.xml',
            '<root><!-- a comment --><node/></root>',
        );
    }
}
