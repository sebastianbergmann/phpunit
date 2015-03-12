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
 * one or more items that match the underlying constraints.
 *
 * Iterates traversable (array or object or Traversable instance) and delegates
 * evaluation of evey item it contains to the underlying constraint instances.
 *
 * Item value and key constraints are passed in the constructor.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Sergii Shymko <sergey@shymko.net>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 */
class PHPUnit_Framework_Constraint_TraversableContainsMatching extends PHPUnit_Framework_Constraint
{
    /**
     * @var PHPUnit_Framework_Constraint
     */
    private $itemConstraint;

    /**
     * @var PHPUnit_Framework_Constraint
     */
    private $keyConstraint;

    /**
     * @param mixed $itemConstraint
     * @param mixed $keyConstraint
     */
    public function __construct(PHPUnit_Framework_Constraint $itemConstraint, PHPUnit_Framework_Constraint $keyConstraint = null)
    {
        parent::__construct();
        $this->itemConstraint = $itemConstraint;
        $this->keyConstraint = $keyConstraint;
    }

    /**
     * Iterate $other and evaluate each item against underlying constraints.
     * Skip evaluation of remaining items as soon as result is deterministic.
     * 
     * {@inheritdoc}
     */
    protected function matches($other)
    {
        $result = false;
        if (is_array($other) || is_object($other) || $other instanceof Traversable) {
            foreach ($other as $key => $item) {
                $result = $this->itemConstraint->evaluate($item, '', true);
                if ($result && $this->keyConstraint) {
                    $result = $this->keyConstraint->evaluate($key, '', true);
                }
                if ($this->isFinalResult($result)) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Whether partial result defines the final result regardless of evaluation
     * of remaining items.
     * 
     * Operand TRUE defines the final result like in the logical operator OR.
     * 
     * @param bool $partialResult
     * @return bool
     */
    protected function isFinalResult($partialResult)
    {
        return $partialResult;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        if ($this->keyConstraint) {
            $result = sprintf(
                $this->getItemKeyMessage(),
                $this->itemConstraint->toString(),
                $this->keyConstraint->toString()
            );
        } else {
            $result = sprintf(
                $this->getItemMessage(),
                $this->itemConstraint->toString()
            );
        }
        return $result;
    }

    /**
     * Return string representation of constraint on item value only.
     * 
     * @return string
     */
    protected function getItemMessage()
    {
        return 'contains an item value of which %s';
    }

    /**
     * Return string representation of constraint on both item value and key.
     *
     * @return string
     */
    protected function getItemKeyMessage()
    {
        return 'contains an item value of which %s and key %s';
    }
}
