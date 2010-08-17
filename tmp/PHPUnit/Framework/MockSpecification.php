<?php

/**
 * @author Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 */
class PHPUnit_Framework_MockSpecification
{
    protected $testCase;
    protected $className;
    protected $methods = array();
    protected $constructorArgs = array();
    protected $originalConstructor = true;
    protected $originalClone = true;

    /**
     * @param PHPUnit_Framework_TestCase
     * @param string
     */
    public function __construct(PHPUnit_Framework_TestCase $testCase,
                                $className)
    {
        $this->testCase = $testCase;
        $this->className = $className;
    }

    public function getMock()
    {
        return $this->testCase->getMock($this->className,
                                        $this->methods,
                                        $this->constructorArgs,
                                        '',
                                        $this->originalConstructor,
                                        $this->originalClone);
    }

    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }

    public function setConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;
    }

    public function disableOriginalConstructor()
    {
        $this->originalConstructor = false;
    } 

    public function disableOriginalClone()
    {
        $this->originalClone = false;
    } 
}
