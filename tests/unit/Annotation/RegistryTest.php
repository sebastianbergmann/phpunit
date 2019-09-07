<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Annotation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;

/**
 * @small
 *
 * @covers \PHPUnit\Annotation\Registry
 * @uses   \PHPUnit\Annotation\DocBlock
 */
final class RegistryTest extends TestCase
{
    public function testRegistryLookupWithExistingClassAnnotation() : void
    {
        $annotation = Registry::singleton()
            ->forClassName(self::class);

        self::assertSame(
            [
                'small'  => [''],
                'covers' => ['\PHPUnit\Annotation\Registry'],
                'uses'   => ['\PHPUnit\Annotation\DocBlock'],
            ],
            $annotation->symbolAnnotations()
        );

        self::assertSame(
            $annotation,
            Registry::singleton()
                ->forClassName(self::class),
            'Registry memoizes retrieved DocBlock instances'
        );
    }

    public function testRegistryLookupWithExistingMethodAnnotation() : void
    {
        $annotation = Registry::singleton()
            ->forMethod(
                \NumericGroupAnnotationTest::class,
                'testTicketAnnotationSupportsNumericValue'
            );

        self::assertSame(
            [
                'testdox' => ['Empty test for @ticket numeric annotation values'],
                'ticket'  => ['3502'],
                'see'     => ['https://github.com/sebastianbergmann/phpunit/issues/3502'],
            ],
            $annotation->symbolAnnotations()
        );

        self::assertSame(
            $annotation,
            Registry::singleton()
                ->forMethod(
                    \NumericGroupAnnotationTest::class,
                    'testTicketAnnotationSupportsNumericValue'
                ),
            'Registry memoizes retrieved DocBlock instances'
        );
    }

    public function testClassLookupForAClassThatDoesNotExistFails() : void
    {
        $registry = Registry::singleton();

        // Note: an exception from the \PHPUnit\Util component is thrown for BC compliance
        $this->expectException(Exception::class);

        $registry->forClassName(\ThisClassDoesNotExist::class);
    }

    public function testMethodLookupForAMethodThatDoesNotExistFails() : void
    {
        $registry = Registry::singleton();

        // Note: an exception from the \PHPUnit\Util component is thrown for BC compliance
        $this->expectException(Exception::class);

        $registry->forMethod(self::class, 'thisMethodDoesNotExist');
    }
}
