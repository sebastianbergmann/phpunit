<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Annotation;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Annotation\Parser\DocBlock;
use PHPUnit\Metadata\AnnotationsAreNotSupportedForInternalClassesException;
use ReflectionClass;
use ReflectionMethod;

#[CoversClass(DocBlock::class)]
#[Small]
#[Group('metadata')]
#[Group('metadata/annotations')]
final class DocBlockTest extends TestCase
{
    public function testDoesNotSupportInternalClasses(): void
    {
        $this->expectException(AnnotationsAreNotSupportedForInternalClassesException::class);

        DocBlock::ofClass(new ReflectionClass(Exception::class));
    }

    public function testDoesNotSupportInternalMethods(): void
    {
        $this->expectException(AnnotationsAreNotSupportedForInternalClassesException::class);

        DocBlock::ofMethod(new ReflectionMethod(Exception::class, 'getMessage'));
    }
}
