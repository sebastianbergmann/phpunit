<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function substr;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class MethodNameFilterCompiler
{
    /**
     * Returns null when the filter does not constrain the method name.
     * Callers should treat null as "cannot prove a mismatch, keep the data provider".
     *
     * Mirrors the parsing in NameFilterIterator::prepareFilter(): the two must
     * stay in lockstep so the early skip never disagrees with the final filter.
     *
     * @return null|non-empty-string
     */
    public static function compile(string $filter): ?string
    {
        if (preg_match('/[a-zA-Z0-9]/', substr($filter, 0, 1)) !== 1 &&
            self::isRegularExpression($filter)) {
            return null;
        }

        if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];
        } elseif (preg_match('/^(.*?)#(.+)$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];
        } elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];
        } else {
            // A filter without a data set portion is matched against the entire
            // test name, including the name of the data set, by NameFilterIterator.
            // It may therefore match a data set name that cannot be known without
            // invoking the data provider (#6741).
            return null;
        }

        if ($methodNamePortion === '') {
            return null;
        }

        return sprintf('{%s}i', $methodNamePortion);
    }

    private static function isRegularExpression(string $filter): bool
    {
        set_error_handler(static fn (): bool => true);

        try {
            return preg_match($filter, '') !== false;
        } finally {
            restore_error_handler();
        }
    }
}
