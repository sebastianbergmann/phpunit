<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue244Test extends TestCase
{
    /**
     * @expectedException Issue244Exception
     * @expectedExceptionCode 123StringCode
     */
    public function testWorks(): void
    {
        throw new Issue244Exception;
    }

    /**
     * @expectedException Issue244Exception
     * @expectedExceptionCode OtherString
     */
    public function testFails(): void
    {
        throw new Issue244Exception;
    }

    /**
     * @expectedException Issue244Exception
     * @expectedExceptionCode 123
     */
    public function testFailsTooIfExpectationIsANumber(): void
    {
        throw new Issue244Exception;
    }

    /**
     * @expectedException Issue244ExceptionIntCode
     * @expectedExceptionCode 123String
     */
    public function testFailsTooIfExceptionCodeIsANumber(): void
    {
        throw new Issue244ExceptionIntCode;
    }
}

class Issue244Exception extends Exception
{
    public function __construct()
    {
        $this->code = '123StringCode';
    }
}

class Issue244ExceptionIntCode extends Exception
{
    public function __construct()
    {
        $this->code = 123;
    }
}
