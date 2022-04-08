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

#[CoversClass(ExtensionLoaded::class)]
final class ExtensionLoadedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $name          = 'example-extension';
        $version       = '1.2.3';

        $event = new ExtensionLoaded(
            $telemetryInfo,
            $name,
            $version
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($name, $event->name());
        $this->assertSame($version, $event->version());
    }
}
