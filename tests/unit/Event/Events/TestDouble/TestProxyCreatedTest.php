<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestDouble;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(TestProxyCreated::class)]
final class TestProxyCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo        = $this->telemetryInfo();
        $className            = self::class;
        $constructorArguments = [
            'foo',
            new stdClass,
            [
                'bar',
                'baz',
            ],
        ];

        $event = new TestProxyCreated(
            $telemetryInfo,
            $className,
            $constructorArguments
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($className, $event->className());
        $this->assertSame($constructorArguments, $event->constructorArguments());
    }
}
