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

use const PHP_EOL;
use function explode;
use function implode;
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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyFalsifiedToStringMapper
{
    private const string INDENTATION = '    ';

    public static function map(PropertyFalsified $event): string
    {
        $examples = self::count($event->numberOfExamplesChecked(), 'example');

        if ($event->counterexample() === []) {
            return sprintf('Property falsified after %s.', $examples) . PHP_EOL;
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

        $buffer .= PHP_EOL . PHP_EOL;

        foreach ($event->counterexample() as $parameter => $value) {
            $buffer .= self::INDENTATION . implode(
                PHP_EOL . self::INDENTATION,
                explode("\n", sprintf('$%s = %s;', $parameter, $value)),
            ) . PHP_EOL;
        }

        if ($event->shrinkingBudgetExhausted()) {
            $buffer .= PHP_EOL . sprintf(
                'Shrinking was stopped after %s, a simpler counterexample may exist.',
                self::count($event->numberOfShrinkingAttempts(), 'attempt'),
            ) . PHP_EOL;
        }

        if ($event->shrinkingInterrupted()) {
            $buffer .= PHP_EOL . sprintf(
                'Shrinking was stopped after %s because the test run was aborted, a simpler counterexample may exist.',
                self::count($event->numberOfShrinkingAttempts(), 'attempt'),
            ) . PHP_EOL;
        }

        return $buffer;
    }

    private static function count(int $number, string $noun): string
    {
        if ($number === 1) {
            return '1 ' . $noun;
        }

        return $number . ' ' . $noun . 's';
    }
}
