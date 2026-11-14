<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use SebastianBergmann\Invoker\TimeoutException;

#[CoversClass(ExampleResult::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ExampleResultTest extends TestCase
{
    /**
     * @return array<string, array{Closure(Falsification, ChoiceSequence): ExampleResult, non-empty-string, bool}>
     */
    public static function resultProvider(): array
    {
        return [
            'passed' => [
                static fn (Falsification $falsification, ChoiceSequence $sequence): ExampleResult => ExampleResult::passed($sequence),
                'passed',
                false,
            ],
            'rejected' => [
                static fn (Falsification $falsification, ChoiceSequence $sequence): ExampleResult => ExampleResult::rejected($sequence),
                'rejected',
                false,
            ],
            'skipped' => [
                static fn (Falsification $falsification, ChoiceSequence $sequence): ExampleResult => ExampleResult::skipped($sequence),
                'skipped',
                false,
            ],
            'incomplete' => [
                static fn (Falsification $falsification, ChoiceSequence $sequence): ExampleResult => ExampleResult::incomplete($sequence),
                'incomplete',
                false,
            ],
            'aborted' => [
                static fn (Falsification $falsification, ChoiceSequence $sequence): ExampleResult => ExampleResult::aborted($falsification, $sequence),
                'aborted',
                true,
            ],
            'failed' => [
                static fn (Falsification $falsification, ChoiceSequence $sequence): ExampleResult => ExampleResult::falsified($falsification, true, $sequence),
                'failed',
                true,
            ],
            'errored' => [
                static fn (Falsification $falsification, ChoiceSequence $sequence): ExampleResult => ExampleResult::falsified($falsification, false, $sequence),
                'errored',
                true,
            ],
        ];
    }

    /**
     * @param Closure(Falsification, ChoiceSequence): ExampleResult $result
     * @param non-empty-string                                      $outcome
     */
    #[DataProvider('resultProvider')]
    public function testHasOutcomeFalsificationAndChoices(Closure $result, string $outcome, bool $hasFalsification): void
    {
        $falsification = Falsification::ofThrowable(new RuntimeException('message'));
        $sequence      = new ChoiceSequence([1, 2, 3]);
        $result        = $result($falsification, $sequence);

        $this->assertSame($outcome, $result->outcome());
        $this->assertSame($sequence, $result->sequence());

        if ($hasFalsification) {
            $this->assertSame($falsification, $result->falsification());
        } else {
            $this->assertNull($result->falsification());
        }
    }

    public function testExampleThatExceededItsTimeLimitTimedOutWhetherItFailedOrErrored(): void
    {
        $falsification = Falsification::ofThrowable(new TimeoutException('Execution aborted after 1 second'));
        $sequence      = new ChoiceSequence([1]);

        $this->assertSame('timeout', ExampleResult::falsified($falsification, true, $sequence)->outcome());
        $this->assertSame('timeout', ExampleResult::falsified($falsification, false, $sequence)->outcome());
    }

    public function testHasNoExportedValuesUnlessTheyWereExported(): void
    {
        $result = ExampleResult::passed(new ChoiceSequence([1]));

        $this->assertNull($result->arguments());
        $this->assertNull($result->drawnValues());
        $this->assertNull($result->steps());
    }

    public function testCanHaveExportedValues(): void
    {
        $falsification = Falsification::ofThrowable(new RuntimeException('message'));
        $sequence      = new ChoiceSequence([1, 2]);
        $result        = ExampleResult::falsified($falsification, true, $sequence)->withExportedValues(
            ['value' => '1'],
            [['label' => 'size', 'value' => '2']],
            ['push(2)'],
        );

        $this->assertSame('failed', $result->outcome());
        $this->assertSame($falsification, $result->falsification());
        $this->assertSame($sequence, $result->sequence());
        $this->assertSame(['value' => '1'], $result->arguments());
        $this->assertSame([['label' => 'size', 'value' => '2']], $result->drawnValues());
        $this->assertSame(['push(2)'], $result->steps());
    }
}
