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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Annotation\Parser\DocBlock;
use PHPUnit\Metadata\Annotation\Parser\Registry;
use PHPUnit\Metadata\ReflectionException;
use PHPUnit\TestFixture\Metadata\Annotation\CoversTest;
use PHPUnit\TestFixture\NumericGroupAnnotationTest;
use ThisClassDoesNotExist;

#[CoversClass(Registry::class)]
#[UsesClass(DocBlock::class)]
#[Small]
#[Group('metadata')]
#[Group('metadata/annotations')]
final class RegistryTest extends TestCase
{
    public function testRegistryLookupWithExistingClassAnnotation(): void
    {
        $annotation = Registry::getInstance()->forClassName(CoversTest::class);

        $this->assertSame(
            [
                'covers' => [
                    '::\PHPUnit\TestFixture\Metadata\Annotation\f()',
                    '\PHPUnit\TestFixture\Metadata\Annotation\Example',
                ],
                'coversNothing'      => [''],
                'coversDefaultClass' => [
                    '\PHPUnit\TestFixture\Metadata\Annotation\Example',
                ],
            ],
            $annotation->symbolAnnotations(),
        );

        $this->assertSame(
            $annotation,
            Registry::getInstance()->forClassName(CoversTest::class),
            'Registry memoizes retrieved DocBlock instances',
        );
    }

    public function testRegistryLookupWithExistingMethodAnnotation(): void
    {
        $annotation = Registry::getInstance()->forMethod(
            NumericGroupAnnotationTest::class,
            'testTicketAnnotationSupportsNumericValue',
        );

        $this->assertSame(
            [
                'testdox' => ['Empty test for @ticket numeric annotation values'],
                'ticket'  => ['3502'],
                'see'     => ['https://github.com/sebastianbergmann/phpunit/issues/3502'],
            ],
            $annotation->symbolAnnotations(),
        );

        $this->assertSame(
            $annotation,
            Registry::getInstance()->forMethod(
                NumericGroupAnnotationTest::class,
                'testTicketAnnotationSupportsNumericValue',
            ),
            'Registry memoizes retrieved DocBlock instances',
        );
    }

    public function testClassLookupForAClassThatDoesNotExistFails(): void
    {
        $registry = Registry::getInstance();

        $this->expectException(ReflectionException::class);

        $registry->forClassName(ThisClassDoesNotExist::class);
    }

    public function testMethodLookupForAMethodThatDoesNotExistFails(): void
    {
        $registry = Registry::getInstance();

        $this->expectException(ReflectionException::class);

        $registry->forMethod(self::class, 'thisMethodDoesNotExist');
    }
}
