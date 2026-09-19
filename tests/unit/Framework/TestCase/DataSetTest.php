<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataSet::class)]
#[Small]
final class DataSetTest extends TestCase
{
    public function testEmptyDataSetHasNoNameAndNoData(): void
    {
        $dataSet = DataSet::empty();

        $this->assertTrue($dataSet->isEmpty());
        $this->assertSame('', $dataSet->name());
        $this->assertSame([], $dataSet->data());
        $this->assertSame('', $dataSet->asString());
        $this->assertSame('', $dataSet->asStringWithData());
    }

    public function testDataSetWithoutDataIsEmpty(): void
    {
        $dataSet = new DataSet('my data set', []);

        $this->assertTrue($dataSet->isEmpty());
        $this->assertSame('my data set', $dataSet->name());
        $this->assertSame('', $dataSet->asString());
        $this->assertSame('', $dataSet->asStringWithData());
    }

    public function testDataSetWithDataIsNotEmpty(): void
    {
        $dataSet = new DataSet(0, ['a', 1]);

        $this->assertFalse($dataSet->isEmpty());
        $this->assertSame(0, $dataSet->name());
        $this->assertSame(['a', 1], $dataSet->data());
    }

    public function testCanBeRepresentedAsStringWhenNameIsNumeric(): void
    {
        $dataSet = new DataSet(0, ['a', 1]);

        $this->assertSame(' with data set #0', $dataSet->asString());
        $this->assertSame("#0 with data ('a', 1)", $dataSet->asStringWithData());
    }

    public function testCanBeRepresentedAsStringWhenNameIsString(): void
    {
        $dataSet = new DataSet('my data set', ['a', 1]);

        $this->assertSame(' with data set "my data set"', $dataSet->asString());
        $this->assertSame("@my data set with data ('a', 1)", $dataSet->asStringWithData());
    }

    public function testBidirectionalControlCharactersInNameAreSanitized(): void
    {
        $dataSet = new DataSet("a\u{202E}b", ['a']);

        $this->assertSame(' with data set "a\u{202E}b"', $dataSet->asString());
        $this->assertSame("@a\\u{202E}b with data ('a')", $dataSet->asStringWithData());
    }
}
