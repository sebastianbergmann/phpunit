<?php

class PHPUnit_Framework_Constraint_NotException extends PHPUnit_Framework_Constraint
{

    /**
     * @return boolean
     * @param Closure $other
     */
    public function evaluate($other)
    {
        try {
            $other();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'no exception should throw';
    }

    /**
     * @return string
     * @param mixed   $other
     * @param string  $description
     * @param boolean $not
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return 'Failed asserting that ' . $this->toString() . '. ' . $description . '.';
    }

}