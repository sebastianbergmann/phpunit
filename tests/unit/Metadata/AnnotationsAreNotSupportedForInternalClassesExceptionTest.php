<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnnotationsAreNotSupportedForInternalClassesException::class)]
#[Small]
#[Group('metadata')]
#[Group('metadata/annotations')]
final class AnnotationsAreNotSupportedForInternalClassesExceptionTest extends TestCase
{
    public function testConstructsMessageCorrectly(): void
    {
        $this->assertSame(
            'Annotations can only be parsed for user-defined classes, trying to parse annotations for class "the-class"',
            (new AnnotationsAreNotSupportedForInternalClassesException('the-class'))->getMessage(),
        );
    }
}
