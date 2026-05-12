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
            @preg_match($filter, '') !== false) {
            return null;
        }

        $methodNamePortion = $filter;

        if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];
        } elseif (preg_match('/^(.*?)#(.+)$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];
        } elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches) === 1) {
            $methodNamePortion = $matches[1];
        }

        if ($methodNamePortion === '') {
            return null;
        }

        return sprintf('{%s}i', $methodNamePortion);
    }
}
