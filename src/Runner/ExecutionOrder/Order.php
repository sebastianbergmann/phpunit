<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder;

/**
 * A reordering strategy that can be configured for a test run, named after the
 * configuration token that selects it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This enumeration is not covered by the backward compatibility promise for PHPUnit
 */
enum Order
{
    public static function fromToken(string $token): ?self
    {
        return match ($token) {
            'defects'             => self::Defects,
            'duration-ascending'  => self::DurationAscending,
            'duration-descending' => self::DurationDescending,
            'modified-ascending'  => self::ModifiedAscending,
            'modified-descending' => self::ModifiedDescending,
            'random'              => self::Random,
            'reverse'             => self::Reverse,
            'size-ascending'      => self::SizeAscending,
            'size-descending'     => self::SizeDescending,
            default               => null,
        };
    }

    /**
     * @return non-empty-string
     */
    public function token(): string
    {
        return match ($this) {
            self::Defects            => 'defects',
            self::DurationAscending  => 'duration-ascending',
            self::DurationDescending => 'duration-descending',
            self::ModifiedAscending  => 'modified-ascending',
            self::ModifiedDescending => 'modified-descending',
            self::Random             => 'random',
            self::Reverse            => 'reverse',
            self::SizeAscending      => 'size-ascending',
            self::SizeDescending     => 'size-descending',
        };
    }

    /**
     * Sorting strategies are mutually exclusive: sorting the tests again
     * discards the order the previous sort established. "defects" is not one of
     * them because it is an overlay that preserves the order it is applied to.
     */
    public function isSortingStrategy(): bool
    {
        return $this !== self::Defects;
    }

    /**
     * Whether this order can only be applied when the outcome and duration of
     * the previous test run are known.
     */
    public function requiresTestRunHistory(): bool
    {
        return match ($this) {
            self::Defects            => true,
            self::DurationAscending  => true,
            self::DurationDescending => true,
            default                  => false,
        };
    }
    case Defects;
    case DurationAscending;
    case DurationDescending;
    case ModifiedAscending;
    case ModifiedDescending;
    case Random;
    case Reverse;
    case SizeAscending;
    case SizeDescending;
}
