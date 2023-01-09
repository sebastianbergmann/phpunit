<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use function is_bool;
use function is_scalar;
use function print_r;
use PHPUnit\Framework\ExpectationFailedException;
use Throwable;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ComparisonFailure
{
    private readonly Comparison $comparison;
    private readonly string $diff;

    public static function from(Throwable $t): ?self
    {
        if (!$t instanceof ExpectationFailedException) {
            return null;
        }

        if (!$t->getComparisonFailure()) {
            return null;
        }

        $expectedAsString = $t->getComparisonFailure()->getExpectedAsString();

        if (empty($expectedAsString)) {
            $expectedAsString = self::mapScalarValueToString($t->getComparisonFailure()->getExpected());
        }

        $actualAsString = $t->getComparisonFailure()->getActualAsString();

        if (empty($actualAsString)) {
            $actualAsString = self::mapScalarValueToString($t->getComparisonFailure()->getActual());
        }

        return new self(
            new Comparison(
                $expectedAsString,
                $actualAsString,
            ),
            $t->getComparisonFailure()->getDiff()
        );
    }

    private function __construct(Comparison $comparison, string $diff)
    {
        $this->comparison = $comparison;
        $this->diff       = $diff;
    }

    public function comparison(): Comparison
    {
        return $this->comparison;
    }

    public function diff(): string
    {
        return $this->diff;
    }

    private static function mapScalarValueToString(mixed $value): ?string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return print_r($value, true);
        }

        return null;
    }
}
