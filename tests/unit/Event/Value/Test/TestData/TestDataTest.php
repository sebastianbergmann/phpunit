<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestData::class)]
#[CoversClass(DataFromDataProvider::class)]
#[CoversClass(DataFromTestDependency::class)]
#[Small]
final class TestDataTest extends TestCase
{
    public function testDataCanBeFromDataProvider(): void
    {
        $name                  = 'data-set-name';
        $dataAsString          = 'data-as-string';
        $dataAsStringForOutput = 'data-as-string-for-output';

        $data = DataFromDataProvider::from(
            $name,
            $dataAsString,
            $dataAsStringForOutput,
        );

        $this->assertTrue($data->isFromDataProvider());
        $this->assertFalse($data->isFromTestDependency());
        $this->assertSame($name, $data->dataSetName());
        $this->assertSame($dataAsString, $data->data());
        $this->assertSame($dataAsStringForOutput, $data->dataAsStringForResultOutput());
    }

    public function testDataCanBeFromDependedUponTest(): void
    {
        $dataAsString = 'data-as-string';

        $data = DataFromTestDependency::from($dataAsString);

        $this->assertTrue($data->isFromTestDependency());
        $this->assertFalse($data->isFromDataProvider());
        $this->assertSame($dataAsString, $data->data());
    }
}
