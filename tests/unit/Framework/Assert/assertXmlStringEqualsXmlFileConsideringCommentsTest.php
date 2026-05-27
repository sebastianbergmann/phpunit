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

#[CoversMethod(Assert::class, 'assertXmlStringEqualsXmlFileConsideringComments')]
#[TestDox('assertXmlStringEqualsXmlFileConsideringComments()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertXmlStringEqualsXmlFileConsideringCommentsTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertXmlStringEqualsXmlFileConsideringComments(
            TEST_FILES_PATH . 'xml-with-comments.xml',
            '<root><!-- a comment --><node/></root>',
        );
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlFileConsideringComments(
            TEST_FILES_PATH . 'xml-with-comments.xml',
            '<root><node/></root>',
        );
    }
}
