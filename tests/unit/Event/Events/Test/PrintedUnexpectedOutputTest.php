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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(PrintedUnexpectedOutput::class)]
#[Small]
final class PrintedUnexpectedOutputTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $output        = 'output';

        $event = new PrintedUnexpectedOutput(
            $telemetryInfo,
            $output,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($output, $event->output());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new PrintedUnexpectedOutput(
            $this->telemetryInfo(),
            'output',
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Printed Unexpected Output
output
EOT
            ,
            $event->asString(),
        );
    }
}
