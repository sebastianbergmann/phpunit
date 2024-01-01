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
use function str_replace;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExcludeNameFilterIterator extends NameFilterIterator
{
    /**
     * @psalm-param non-empty-string $filter
     *
     * @psalm-return array{regularExpression: non-empty-string, dataSetMinimum: ?int, dataSetMaximum: ?int}
     */
    protected function prepareFilter(string $filter): array
    {
        if (@preg_match($filter, '') === false) {
            $filter = sprintf(
                '/^(?:(?!%s).)*/i',
                str_replace(
                    '/',
                    '\\/',
                    $filter,
                ),
            );
        }

        return [
            'regularExpression' => $filter,
            'dataSetMinimum'    => null,
            'dataSetMaximum'    => null,
        ];
    }
}
