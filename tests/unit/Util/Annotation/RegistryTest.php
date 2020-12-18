<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Annotation;

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\NumericGroupAnnotationTest;
use PHPUnit\Util\Exception;
use ThisClassDoesNotExist;

/**
 * @small
 *
 * @covers \PHPUnit\Util\Annotation\Registry
 *
 * @uses   \PHPUnit\Util\Annotation\DocBlock
 */
final class RegistryTest extends TestCase
{
    public function testRegistryLookupWithExistingClassAnnotation(): void
    {
        $annotation = Registry::getInstance()->forClassName(self::class);

        $this->assertSame(
            [
                'small'  => [''],
                'covers' => ['\PHPUnit\Util\Annotation\Registry'],
                'uses'   => ['\PHPUnit\Util\Annotation\DocBlock'],
            ],
            $annotation->symbolAnnotations()
        );

        $this->assertSame(
            $annotation,
            Registry::getInstance()->forClassName(self::class),
            'Registry memoizes retrieved DocBlock instances'
        );
    }

    public function testRegistryLookupWithExistingMethodAnnotation(): void
    {
        $annotation = Registry::getInstance()->forMethod(
            NumericGroupAnnotationTest::class,
            'testTicketAnnotationSupportsNumericValue'
        );

        $this->assertSame(
            [
                'testdox' => ['Empty test for @ticket numeric annotation values'],
                'ticket'  => ['3502'],
                'author'  => ['C. Lippy'],
                'see'     => ['https://github.com/sebastianbergmann/phpunit/issues/3502'],
            ],
            $annotation->symbolAnnotations()
        );

        $this->assertSame(
            $annotation,
            Registry::getInstance()->forMethod(
                NumericGroupAnnotationTest::class,
                'testTicketAnnotationSupportsNumericValue'
            ),
            'Registry memoizes retrieved DocBlock instances'
        );
    }

    public function testClassLookupForAClassThatDoesNotExistFails(): void
    {
        $registry = Registry::getInstance();

        $this->expectException(Exception::class);

        $registry->forClassName(ThisClassDoesNotExist::class);
    }

    public function testMethodLookupForAMethodThatDoesNotExistFails(): void
    {
        $registry = Registry::getInstance();

        $this->expectException(Exception::class);

        $registry->forMethod(self::class, 'thisMethodDoesNotExist');
    }
}
