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

use const INF;
use const PHP_INT_MIN;
use function array_is_list;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_nan;
use function is_object;
use function is_string;
use function mb_check_encoding;
use function mb_ord;
use function mb_str_split;
use function ord;
use function preg_match;
use function sprintf;
use function str_split;
use function str_starts_with;
use function strlen;
use function var_export;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Util\Exporter;
use UnitEnum;

/**
 * Exports the arguments of a counterexample as PHP expressions that can be
 * copied into a data provider for a regression test.
 *
 * Scalars and arrays are exported as PHP literals, the cases of enumerations
 * as constant expressions such as Currency::EUR, objects that generators
 * created with new as the expression that created them, and points in time
 * as new DateTimeImmutable('...'). Class names are written the way the file
 * that declares the property imports them, or fully qualified when it does
 * not import them. Other objects are exported with sebastian/exporter.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CounterexampleExporter
{
    private NameScope $scope;
    private Constructions $constructions;

    public function __construct(NameScope $scope, Constructions $constructions)
    {
        $this->scope         = $scope;
        $this->constructions = $constructions;
    }

    public function export(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $this->boolean($value);
        }

        if (is_int($value)) {
            return $this->integer($value);
        }

        if (is_float($value)) {
            return $this->float($value);
        }

        if (is_string($value)) {
            return $this->string($value);
        }

        if (is_array($value)) {
            return $this->array($value);
        }

        if ($value instanceof UnitEnum) {
            return $this->scope->shortName($value::class) . '::' . $value->name;
        }

        if (!is_object($value)) {
            return Exporter::export($value);
        }

        if ($this->constructions->has($value)) {
            return $this->constructed($value);
        }

        if ($value instanceof DateTimeInterface && ($value::class === DateTimeImmutable::class || $value::class === DateTime::class)) {
            return $this->pointInTime($value);
        }

        return Exporter::export($value);
    }

    private function boolean(bool $value): string
    {
        if ($value) {
            return 'true';
        }

        return 'false';
    }

    /**
     * The literal -9223372036854775808 is a float in PHP.
     */
    private function integer(int $value): string
    {
        if ($value === PHP_INT_MIN) {
            return 'PHP_INT_MIN';
        }

        return (string) $value;
    }

    private function float(float $value): string
    {
        if (is_nan($value)) {
            return 'NAN';
        }

        if ($value === INF) {
            return 'INF';
        }

        if ($value === -INF) {
            return '-INF';
        }

        return var_export($value, true);
    }

    /**
     * A string that is valid UTF-8 and does not contain control characters is
     * exported as a single-quoted string, any other string as a double-quoted
     * string with escape sequences for the control characters and, unless it
     * is valid UTF-8, for the bytes that are not printable ASCII characters.
     */
    private function string(string $value): string
    {
        $isUtf8 = mb_check_encoding($value, 'UTF-8');

        if ($isUtf8 && preg_match('/[\x00-\x1F\x7F]|\xC2[\x80-\x9F]/', $value) !== 1) {
            return var_export($value, true);
        }

        if ($isUtf8) {
            $characters = mb_str_split($value, 1, 'UTF-8');
        } else {
            $characters = str_split($value);
        }

        $escaped = '';

        foreach ($characters as $character) {
            $escaped .= $this->escape($character, $isUtf8);
        }

        return '"' . $escaped . '"';
    }

    private function escape(string $character, bool $isUtf8): string
    {
        $escapeSequence = match ($character) {
            "\n"    => '\n',
            "\r"    => '\r',
            "\t"    => '\t',
            "\v"    => '\v',
            "\e"    => '\e',
            "\f"    => '\f',
            '\\'    => '\\\\',
            '"'     => '\"',
            '$'     => '\$',
            default => null,
        };

        if ($escapeSequence !== null) {
            return $escapeSequence;
        }

        if (strlen($character) === 1) {
            $byte = ord($character);

            if ($byte < 0x20 || $byte === 0x7F || (!$isUtf8 && $byte >= 0x80)) {
                return sprintf('\x%02X', $byte);
            }

            return $character;
        }

        $codePoint = mb_ord($character, 'UTF-8');

        if ($codePoint >= 0x80 && $codePoint <= 0x9F) {
            return sprintf('\u{%X}', $codePoint);
        }

        return $character;
    }

    /**
     * @param array<mixed> $value
     */
    private function array(array $value): string
    {
        $elements = [];

        if (array_is_list($value)) {
            foreach ($value as $element) {
                $elements[] = $this->export($element);
            }
        } else {
            foreach ($value as $key => $element) {
                $elements[] = $this->export($key) . ' => ' . $this->export($element);
            }
        }

        return '[' . implode(', ', $elements) . ']';
    }

    private function constructed(object $value): string
    {
        $construction = $this->constructions->of($value);
        $className    = $this->scope->shortName($construction['className']);

        if ($construction['arguments'] === []) {
            return 'new ' . $className;
        }

        $arguments = [];

        foreach ($construction['arguments'] as $name => $argument) {
            $arguments[] = $name . ': ' . $this->export($argument);
        }

        return 'new ' . $className . '(' . implode(', ', $arguments) . ')';
    }

    /**
     * A point in time whose time zone is an offset from UTC, such as the
     * generated ones, is exported with the offset. For any other time zone,
     * the name of the time zone is exported, too.
     */
    private function pointInTime(DateTimeInterface $value): string
    {
        $format = 'Y-m-d\TH:i:s';

        if ($value->format('u') !== '000000') {
            $format .= '.u';
        }

        $timeZone = $value->getTimezone()->getName();

        if (str_starts_with($timeZone, '+') || str_starts_with($timeZone, '-')) {
            return sprintf(
                'new %s(%s)',
                $this->scope->shortName($value::class),
                var_export($value->format($format . 'P'), true),
            );
        }

        return sprintf(
            'new %s(%s, new %s(%s))',
            $this->scope->shortName($value::class),
            var_export($value->format($format), true),
            $this->scope->shortName(DateTimeZone::class),
            var_export($timeZone, true),
        );
    }
}
