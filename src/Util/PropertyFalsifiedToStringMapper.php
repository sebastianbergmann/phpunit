<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function explode;
use function implode;
use function is_int;
use function sprintf;
use PHPUnit\Event\Test\PropertyFalsified;

/**
 * Describes how a property was falsified, with its minimal counterexample
 * written as PHP code that can be copied into a data provider, for instance:
 *
 *     Property falsified after 2 examples, counterexample shrunk in 17 steps:
 *
 *         $money = new Money(amount: 1, currency: Currency::EUR);
 *         $parts = 2;
 *
 * The values that the minimal counterexample drew with draw() follow its
 * arguments, numbered in the order in which they were drawn and described by
 * their labels:
 *
 *     Property falsified after 8 examples, counterexample shrunk in 2 steps:
 *
 *         $size = 1;
 *
 *         Draw 1 (list): [5]
 *         Draw 2 (index): 0
 *
 * The steps of the state machine that the minimal counterexample ran with
 * runStateMachine() follow, as calls of the methods of the state machine,
 * which is referred to as $machine:
 *
 *     Property falsified after 3 examples, counterexample shrunk in 25 steps:
 *
 *         $accounts1 = $machine->open();
 *         $machine->deposit(account: $accounts1, amount: 1);
 *         $machine->projectionMatchesTheModel(); // invariant failed
 *
 * When the property was falsified in other ways while the counterexample was
 * shrunk, the simplest counterexample for each of them follows:
 *
 *     Property also falsified by RuntimeException at /path/to/FooTest.php:12 with this counterexample:
 *
 *         $size = 3;
 *
 * A property can also be falsified by one of its explicit examples, which is
 * reported with its arguments:
 *
 *     Property falsified by explicit example "minus one":
 *
 *         $value = -1;
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyFalsifiedToStringMapper
{
    private const string INDENTATION = '    ';

    public static function map(PropertyFalsified $event): string
    {
        $explicitExample = $event->explicitExample();

        if ($explicitExample !== null) {
            return self::mapExplicitExample($event, $explicitExample);
        }

        $examples = self::count($event->numberOfExamplesChecked(), 'example');

        if ($event->counterexample() === [] && $event->drawnValues() === [] && $event->steps() === []) {
            return sprintf('Property falsified after %s.', $examples) . "\n";
        }

        if ($event->numberOfShrinkingSteps() === 0) {
            $buffer = sprintf('Property falsified after %s with this counterexample:', $examples);
        } else {
            $buffer = sprintf(
                'Property falsified after %s, counterexample shrunk in %s:',
                $examples,
                self::count($event->numberOfShrinkingSteps(), 'step'),
            );
        }

        $buffer .= "\n\n" . self::arguments($event->counterexample(), $event->drawnValues(), $event->steps());

        if ($event->shrinkingBudgetExhausted()) {
            $buffer .= "\n" . sprintf(
                'Shrinking was stopped after %s, a simpler counterexample may exist.',
                self::count($event->numberOfShrinkingAttempts(), 'attempt'),
            ) . "\n";
        }

        if ($event->shrinkingInterrupted()) {
            $buffer .= "\n" . sprintf(
                'Shrinking was stopped after %s because the test run was aborted, a simpler counterexample may exist.',
                self::count($event->numberOfShrinkingAttempts(), 'attempt'),
            ) . "\n";
        }

        if ($event->numberOfReproductions() < $event->numberOfReplays()) {
            $buffer .= "\n" . self::reproductions($event->numberOfReproductions(), $event->numberOfReplays()) . "\n";
        }

        foreach ($event->otherFalsifications() as $otherFalsification) {
            $buffer .= "\n" . sprintf('Property also falsified by %s with this counterexample:', $otherFalsification['description']) . "\n\n";
            $buffer .= self::arguments($otherFalsification['counterexample'], $otherFalsification['drawnValues'], $otherFalsification['steps']);
        }

        return $buffer;
    }

    /**
     * @param int|non-empty-string $explicitExample
     */
    private static function mapExplicitExample(PropertyFalsified $event, int|string $explicitExample): string
    {
        if (is_int($explicitExample)) {
            $label = '#' . $explicitExample;
        } else {
            $label = sprintf('"%s"', $explicitExample);
        }

        if ($event->counterexample() === []) {
            return sprintf('Property falsified by explicit example %s.', $label) . "\n";
        }

        return sprintf('Property falsified by explicit example %s:', $label) . "\n\n" . self::arguments($event->counterexample(), [], []);
    }

    /**
     * @param array<non-empty-string, string>                      $counterexample
     * @param list<array{label: ?non-empty-string, value: string}> $drawnValues
     * @param list<non-empty-string>                               $steps
     */
    private static function arguments(array $counterexample, array $drawnValues, array $steps): string
    {
        $buffer = '';

        foreach ($counterexample as $parameter => $value) {
            $buffer .= self::indented(sprintf('$%s = %s;', $parameter, $value));
        }

        if ($buffer !== '' && $drawnValues !== []) {
            $buffer .= "\n";
        }

        foreach ($drawnValues as $index => $drawnValue) {
            if ($drawnValue['label'] === null) {
                $description = sprintf('Draw %d', $index + 1);
            } else {
                $description = sprintf('Draw %d (%s)', $index + 1, $drawnValue['label']);
            }

            $buffer .= self::indented($description . ': ' . $drawnValue['value']);
        }

        if ($buffer !== '' && $steps !== []) {
            $buffer .= "\n";
        }

        foreach ($steps as $step) {
            $buffer .= self::indented($step);
        }

        return $buffer;
    }

    private static function indented(string $lines): string
    {
        return self::INDENTATION . implode("\n" . self::INDENTATION, explode("\n", $lines)) . "\n";
    }

    /**
     * @param non-negative-int $numberOfReproductions
     * @param positive-int     $numberOfReplays
     */
    private static function reproductions(int $numberOfReproductions, int $numberOfReplays): string
    {
        if ($numberOfReproductions === 0) {
            return sprintf(
                'The example that falsified the property did not falsify it again in %s.',
                self::count($numberOfReplays, 'replay'),
            );
        }

        return sprintf(
            'The example that falsified the property falsified it again in only %d of %s, so its outcome does not depend on its arguments alone.',
            $numberOfReproductions,
            self::count($numberOfReplays, 'replay'),
        );
    }

    private static function count(int $number, string $noun): string
    {
        if ($number === 1) {
            return '1 ' . $noun;
        }

        return $number . ' ' . $noun . 's';
    }
}
