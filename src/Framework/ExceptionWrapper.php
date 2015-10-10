<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

/**
 * Wraps Exceptions thrown by code under test.
 *
 * Re-instantiates Exceptions thrown by user-space code to retain their original
 * class names, properties, and stack traces (but without arguments).
 *
 * Unlike PHPUnit_Framework_Exception, the complete stack of previous Exceptions
 * is processed.
 *
 * @since Class available since Release 4.3.0
 */
class PHPUnit_Framework_ExceptionWrapper extends PHPUnit_Framework_Exception
{
    /**
     * @var string
     */
    protected $classname;

    /**
     * @var PHPUnit_Framework_ExceptionWrapper|null
     */
    protected $previous;

    /**
     * @param Throwable $t
     */
    public function __construct(Throwable $t)
    {
        // PDOException::getCode() is a string.
        // @see http://php.net/manual/en/class.pdoexception.php#95812
        parent::__construct($t->getMessage(), (int) $t->getCode());

        $this->classname = get_class($t);
        $this->file      = $t->getFile();
        $this->line      = $t->getLine();

        $this->serializableTrace = $t->getTrace();

        foreach ($this->serializableTrace as $i => $call) {
            unset($this->serializableTrace[$i]['args']);
        }

        if ($t->getPrevious()) {
            $this->previous = new self($t->getPrevious());
        }
    }

    /**
     * @return string
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * @return PHPUnit_Framework_ExceptionWrapper
     */
    public function getPreviousWrapped()
    {
        return $this->previous;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = PHPUnit_Framework_TestFailure::exceptionToString($this);

        if ($trace = PHPUnit_Util_Filter::getFilteredStacktrace($this)) {
            $string .= "\n" . $trace;
        }

        if ($this->previous) {
            $string .= "\nCaused by\n" . $this->previous;
        }

        return $string;
    }
}
