<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Util\RegularExpression;
use PHPUnit\Util\Test;
use RecursiveFilterIterator;
use RecursiveIterator;

class NameFilterIterator extends RecursiveFilterIterator
{
    /**
     * @var string
     */
    protected $filter;

    /**
     * @var int
     */
    protected $filterMin;
    /**
     * @var int
     */
    protected $filterMax;

    /**
     * @param RecursiveIterator $iterator
     * @param string            $filter
     *
     * @throws \Exception
     */
    public function __construct(RecursiveIterator $iterator, $filter)
    {
        parent::__construct($iterator);
        $this->setFilter($filter);
    }

    /**
     * @return bool
     */
    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        $tmp = Test::describe($test);

        if ($test instanceof WarningTestCase) {
            $name = $test->getMessage();
        } else {
            if ($tmp[0] != '') {
                $name = \implode('::', $tmp);
            } else {
                $name = $tmp[1];
            }
        }

        $accepted = @\preg_match($this->filter, $name, $matches);

        if ($accepted && isset($this->filterMax)) {
            $set      = \end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }

        return $accepted;
    }

    /**
     * @param string $filter
     *
     * @throws \Exception
     */
    protected function setFilter($filter): void
    {
        if (RegularExpression::safeMatch($filter, '') === false) {
            // Handles:
            //  * testAssertEqualsSucceeds#4
            //  * testAssertEqualsSucceeds#4-8
            if (\preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches)) {
                if (isset($matches[3]) && $matches[2] < $matches[3]) {
                    $filter = \sprintf(
                        '%s.*with data set #(\d+)$',
                        $matches[1]
                    );

                    $this->filterMin = $matches[2];
                    $this->filterMax = $matches[3];
                } else {
                    $filter = \sprintf(
                        '%s.*with data set #%s$',
                        $matches[1],
                        $matches[2]
                    );
                }
            } // Handles:
            //  * testDetermineJsonError@JSON_ERROR_NONE
            //  * testDetermineJsonError@JSON.*
            elseif (\preg_match('/^(.*?)@(.+)$/', $filter, $matches)) {
                $filter = \sprintf(
                    '%s.*with data set "%s"$',
                    $matches[1],
                    $matches[2]
                );
            }

            // Escape delimiters in regular expression. Do NOT use preg_quote,
            // to keep magic characters.
            $filter = \sprintf('/%s/', \str_replace(
                '/',
                '\\/',
                $filter
            ));
        }

        $this->filter = $filter;
    }
}
