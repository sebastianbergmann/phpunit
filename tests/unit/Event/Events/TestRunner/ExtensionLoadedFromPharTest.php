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

#[CoversClass(ExtensionLoadedFromPhar::class)]
#[Small]
final class ExtensionLoadedFromPharTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $filename      = 'extension.phar';
        $name          = 'example-extension';
        $version       = '1.2.3';

        $event = new ExtensionLoadedFromPhar(
            $telemetryInfo,
            $filename,
            $name,
            $version,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($filename, $event->filename());
        $this->assertSame($name, $event->name());
        $this->assertSame($version, $event->version());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new ExtensionLoadedFromPhar(
            $this->telemetryInfo(),
            'extension.phar',
            'example-extension',
            '1.2.3',
        );

        $this->assertSame('Extension Loaded from PHAR (example-extension 1.2.3)', $event->asString());
    }
}
