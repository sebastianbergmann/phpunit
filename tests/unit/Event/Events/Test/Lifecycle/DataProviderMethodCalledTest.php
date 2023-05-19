<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(DataProviderMethodCalled::class)]
#[Small]
final class DataProviderMethodCalledTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo      = $this->telemetryInfo();
        $testMethod         = new ClassMethod('ClassTest', 'testOne');
        $dataProviderMethod = new ClassMethod('ClassTest', 'dataProvider');

        $event = new DataProviderMethodCalled(
            $telemetryInfo,
            $testMethod,
            $dataProviderMethod,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testMethod, $event->testMethod());
        $this->assertSame($dataProviderMethod, $event->dataProviderMethod());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new DataProviderMethodCalled(
            $this->telemetryInfo(),
            new ClassMethod('ClassTest', 'testOne'),
            new ClassMethod('ClassTest', 'dataProvider'),
        );

        $this->assertSame('Data Provider Method Called (ClassTest::dataProvider for test method ClassTest::testOne)', $event->asString());
    }
}
