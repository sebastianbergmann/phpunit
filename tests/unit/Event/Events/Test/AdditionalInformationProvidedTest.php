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

#[CoversClass(AdditionalInformationProvided::class)]
#[Small]
final class AdditionalInformationProvidedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo         = $this->telemetryInfo();
        $test                  = $this->testValueObject();
        $additionalInformation = 'additional information';

        $event = new AdditionalInformationProvided(
            $telemetryInfo,
            $test,
            $additionalInformation,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($additionalInformation, $event->additionalInformation());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new AdditionalInformationProvided(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'additional information',
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Provided Additional Information
additional information
EOT
            ,
            $event->asString(),
        );
    }
}
