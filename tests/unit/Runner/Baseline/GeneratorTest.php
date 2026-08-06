<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(Generator::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/baseline')]
final class GeneratorTest extends AbstractEventTestCase
{
    #[TestDox('Does not add a warning that was triggered in code that is not first-party code when warnings are restricted')]
    public function testDoesNotAddRestrictedWarningThatWasNotTriggeredInFirstPartyCode(): void
    {
        $generator = new Generator(new Facade, $this->source(true));

        $generator->testTriggeredIssue(
            new WarningTriggered(
                $this->telemetryInfo(),
                $this->testValueObject(),
                'warning message',
                __FILE__,
                1,
                false,
                false,
            ),
        );

        $this->assertSame([], $generator->baseline()->groupedByFileAndLine());
    }

    private function source(bool $restrictWarnings): Source
    {
        return new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            false,
            $restrictWarnings,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions'               => [],
                'methods'                 => [],
                'ignoreUndefinedTriggers' => true,
            ],
            false,
            false,
            false,
            false,
        );
    }
}
