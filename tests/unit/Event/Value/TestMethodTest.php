<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;

/**
 * @covers \PHPUnit\Event\Code\TestMethod
 */
final class TestMethodTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $className             = self::class;
        $methodName            = 'foo';
        $methodNameWithDataSet = 'foo with data set #123';
        $dataSet               = 'foo with data set #123 (...)';
        $metadata              = MetadataCollection::fromArray([]);

        $test = new TestMethod(
            $className,
            $methodName,
            $methodNameWithDataSet,
            $dataSet,
            'unknown',
            0,
            $metadata
        );

        $this->assertSame($className, $test->className());
        $this->assertSame($methodName, $test->methodName());
        $this->assertSame($methodNameWithDataSet, $test->dataSetName());
        $this->assertSame($dataSet, $test->dataSetAsString());
        $this->assertSame($metadata, $test->metadata());
    }
}
