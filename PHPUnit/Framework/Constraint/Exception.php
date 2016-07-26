<?php

class PHPUnit_Framework_Constraint_Exception extends PHPUnit_Framework_Constraint
{

    /**
     * @var string
     */
    protected $exceptionClass;

    /**
     * @var string
     */
    protected $exceptionMessage;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @param string $exceptionClass
     * @param string $exceptionMessage
     */
    public function __construct($exceptionClass, $exceptionMessage)
    {
        $this->exceptionClass = $exceptionClass;
        $this->exceptionMessage = $exceptionMessage;
    }

    /**
     * @return boolean
     * @param Closure $other
     */
    public function evaluate($other)
    {
        $this->description = null;
        try {
            $other();
        } catch (Exception $e) {
            if ($e instanceof $this->exceptionClass) {
                if (null === $this->exceptionMessage) {
                    return true;
                }
                if (false === strIPos($e->getMessage(), $this->exceptionMessage)) {
                    $this->description = 'exception message not matches';
                    return false;
                }
                return true;
            } else {
                $this->description = 'exception class not matches';
                return false;
            }
        }
        $this->description = 'no exception thrown';
        return false;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'exception <' . $this->exceptionClass . '>' . (null === $this->exceptionMessage ? '' : ' with message contains "' . $this->exceptionMessage . '"');
    }

    /**
     * @return string
     * @param mixed   $other
     * @param string  $description
     * @param boolean $not
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return 'Failed asserting that ' . $this->toString() . ' should throw' . (null === $this->description ? '' : ' (' . $this->description . ')') . '. ' . $description . '.';
    }

}