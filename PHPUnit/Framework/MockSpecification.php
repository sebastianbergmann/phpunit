<?php

/**
 * @author Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 */
class PHPUnit_Framework_MockSpecification
{
    protected $testCase;
    protected $className;
    protected $methods = array();
    protected $mockClassName = '';
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

    /**
     * @return object
     */
    public function getMock()
    {
        return $this->testCase->getMock($this->className,
                                        $this->methods,
                                        $this->constructorArgs,
                                        $this->mockClassName,
                                        $this->originalConstructor,
                                        $this->originalClone);
    }

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function setConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;
        return $this;
    }

    /**
     * @param string
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function setMockClassName($name)
    {
        $this->mockClassName = $name;
        return $this;
    }

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function disableOriginalConstructor()
    {
        $this->originalConstructor = false;
        return $this;
    } 

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function disableOriginalClone()
    {
        $this->originalClone = false;
        return $this;
    } 
}
