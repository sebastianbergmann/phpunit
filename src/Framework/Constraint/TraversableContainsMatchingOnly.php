<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Constraint that asserts that the traversable it is evaluated for contains
 * only items that match the underlying constraints.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Sergii Shymko <sergey@shymko.net>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 */
class PHPUnit_Framework_Constraint_TraversableContainsMatchingOnly
    extends PHPUnit_Framework_Constraint_TraversableContainsMatching
{
    /**
     * Whether partial result defines the final result regardless of evaluation
     * of remaining items.
     *
     * Operand FALSE defines the final result like in the logical operator AND.
     *
     * @param bool $partialResult
     * @return bool
     */
    protected function isFinalResult($partialResult)
    {
        return !$partialResult;
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemMessage()
    {
        return 'contains only items value of each of which %s';
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemKeyMessage()
    {
        return 'contains only items value of each of which %s and key %s';
    }
}
