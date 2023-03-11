<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(ExtensionBootstrapped::class)]
#[Small]
final class ExtensionBootstrappedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $className     = 'the-className';
        $parameters    = ['foo' => 'bar'];

        $event = new ExtensionBootstrapped(
            $telemetryInfo,
            $className,
            $parameters,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($className, $event->className());
        $this->assertSame($parameters, $event->parameters());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new ExtensionBootstrapped(
            $this->telemetryInfo(),
            'the-className',
            [],
        );

        $this->assertSame('Extension Bootstrapped (the-className)', $event->asString());
    }
}
