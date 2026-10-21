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
use function is_int;
use function sprintf;
use function usort;
use PHPUnit\Event\Test\PropertyChecked;

/**
 * Describes the statistics of a property: how many of its examples were
 * checked and rejected, the percentage of the examples that were checked
 * that were classified with each label, from the largest to the smallest
 * percentage, and the statistics of the values that were observed with each
 * label, for instance:
 *
 *     100 examples checked, 3 rejected
 *
 *          52.0% even
 *          48.0% odd
 *           1.0% zero
 *
 *     size: count 100, min 0, median 4, mean 5.12, p90 11, max 32
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyCheckedToStringMapper
{
    private const string INDENTATION = '    ';

    public static function map(PropertyChecked $event): string
    {
        $buffer = sprintf(
            '%d example%s checked, %d rejected',
            $event->numberOfExamplesChecked(),
            self::plural($event->numberOfExamplesChecked()),
            $event->numberOfExamplesRejected(),
        ) . PHP_EOL;

        return $buffer . self::classifications($event) . self::observations($event);
    }

    private static function classifications(PropertyChecked $event): string
    {
        $classifications = $event->classifications();

        if ($classifications === []) {
            return '';
        }

        usort(
            $classifications,
            static fn (array $a, array $b): int => $b['numberOfExamples'] <=> $a['numberOfExamples'],
        );

        $buffer = PHP_EOL;

        foreach ($classifications as $classification) {
            $buffer .= self::INDENTATION . sprintf(
                '%5.1f%% %s',
                100 * $classification['numberOfExamples'] / $event->numberOfExamplesChecked(),
                $classification['label'],
            ) . PHP_EOL;
        }

        return $buffer;
    }

    private static function observations(PropertyChecked $event): string
    {
        if ($event->observations() === []) {
            return '';
        }

        $buffer = PHP_EOL;

        foreach ($event->observations() as $observation) {
            $buffer .= self::INDENTATION . sprintf(
                '%s: count %d, min %s, median %s, mean %s, p90 %s, max %s',
                $observation['label'],
                $observation['numberOfValues'],
                self::number($observation['minimum']),
                self::number($observation['median']),
                self::number($observation['mean']),
                self::number($observation['percentile90']),
                self::number($observation['maximum']),
            ) . PHP_EOL;
        }

        return $buffer;
    }

    /**
     * Formats an integer as it is, and a float with at most six significant
     * digits.
     */
    private static function number(float|int $number): string
    {
        if (is_int($number)) {
            return (string) $number;
        }

        return sprintf('%.6g', $number);
    }

    private static function plural(int $number): string
    {
        if ($number === 1) {
            return '';
        }

        return 's';
    }
}
