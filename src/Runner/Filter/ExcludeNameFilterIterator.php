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
use Exception;

class ExcludeNameFilterIterator extends NameFilterIterator
{
    /**
     * @throws Exception
     */
    protected function setFilter(string $filter): void
    {
        if (@preg_match($filter, '') === false) {
            // Handles:
            //  * testAssertEqualsSucceeds#4
            //  * testAssertEqualsSucceeds#4-8
            if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches)) {
                if (isset($matches[3]) && $matches[2] < $matches[3]) {
                    $filter = sprintf(
                        '^(?:(?!%s).)*with data set #(\d+)$',
                        $matches[1],
                    );

                    $this->filterMin = (int) $matches[2];
                    $this->filterMax = (int) $matches[3];
                } else {
                    // if the exclude-filter is name it will match anything that doesn't contain name
                    $filter = sprintf(
                        '^(?:(?!%s).)*with data set #%s$',
                        $matches[1],
                        $matches[2],
                    );
                }
            } // Handles:
            //  * testDetermineJsonError@JSON_ERROR_NONE
            //  * testDetermineJsonError@JSON.*
            elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches)) {
                $filter = sprintf(
                    '^(?:(?!%s).)*with data set "%s"$',
                    $matches[1],
                    $matches[2],
                );
            }

            // Escape delimiters in regular expression. Do NOT use preg_quote,
            // to keep magic characters.
            $filter = sprintf(
                '/^(?:(?!%s).)*/i',
                str_replace(
                    '/',
                    '\\/',
                    $filter,
                ),
            );
        }

        $this->filter = $filter;
    }
}
