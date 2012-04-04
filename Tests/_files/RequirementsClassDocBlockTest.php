<?php

/**
 * @requires PHP 5.3
 * @requires PHPUnit 3.8
 * @requires function testFuncClass
 * @requires extension testExtClass
 */
class RequirementsClassDocBlockTest {

    /**
     * @requires PHP 5.4
     * @requires PHPUnit 3.7
     * @requires function testFuncMethod
     * @requires extension testExtMethod
     */
    public function testMethod()
    {
    }

}


