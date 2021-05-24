<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Benchmark;

use PHPUnit\Benchmark\TestFixture\AnnotationTest;
use PHPUnit\Benchmark\TestFixture\AttributeTest;
use PHPUnit\Metadata\Parser\AnnotationParser;
use PHPUnit\Metadata\Parser\AttributeParser;

final class MetadataBench
{
    public function benchAttributeParsing(): void
    {
        /** @noinspection UnusedFunctionResultInspection */
        (new AttributeParser)->forMethod(AttributeTest::class, 'one');
    }

    public function benchAnnotationParsing(): void
    {
        /** @noinspection UnusedFunctionResultInspection */
        (new AnnotationParser)->forMethod(AnnotationTest::class, 'one');
    }
}
