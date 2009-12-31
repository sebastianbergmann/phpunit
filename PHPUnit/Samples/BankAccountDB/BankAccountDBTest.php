<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';

require_once 'BankAccount.php';

/**
 * Tests for the BankAccount class.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class BankAccountDBTest extends PHPUnit_Extensions_Database_TestCase
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('sqlite::memory:');
        BankAccount::createTable($this->pdo);
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->pdo, 'sqlite');
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/bank-account-seed.xml');
    }

    public function testNewAccountBalanceIsInitiallyZero()
    {
        $bank_account = new BankAccount('12345678912345678', $this->pdo);
        $this->assertEquals(0, $bank_account->getBalance());
    }

    public function testOldAccountInfoInitiallySet()
    {
        $bank_account = new BankAccount('15934903649620486', $this->pdo);
        $this->assertEquals(100, $bank_account->getBalance());
        $this->assertEquals('15934903649620486', $bank_account->getAccountNumber());

        $bank_account = new BankAccount('15936487230215067', $this->pdo);
        $this->assertEquals(1216, $bank_account->getBalance());
        $this->assertEquals('15936487230215067', $bank_account->getAccountNumber());

        $bank_account = new BankAccount('12348612357236185', $this->pdo);
        $this->assertEquals(89, $bank_account->getBalance());
        $this->assertEquals('12348612357236185', $bank_account->getAccountNumber());
    }

    public function testAccountBalanceDeposits()
    {
        $bank_account = new BankAccount('15934903649620486', $this->pdo);
        $bank_account->depositMoney(100);

        $bank_account = new BankAccount('15936487230215067', $this->pdo);
        $bank_account->depositMoney(230);

        $bank_account = new BankAccount('12348612357236185', $this->pdo);
        $bank_account->depositMoney(24);

           $xml_dataset = $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/bank-account-after-deposits.xml');
        $this->assertDataSetsEqual($xml_dataset, $this->getConnection()->createDataSet());
    }

    public function testAccountBalanceWithdrawals()
    {
        $bank_account = new BankAccount('15934903649620486', $this->pdo);
        $bank_account->withdrawMoney(100);

        $bank_account = new BankAccount('15936487230215067', $this->pdo);
        $bank_account->withdrawMoney(230);

        $bank_account = new BankAccount('12348612357236185', $this->pdo);
        $bank_account->withdrawMoney(24);

        $xml_dataset = $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/bank-account-after-withdrawals.xml');
        $this->assertDataSetsEqual($xml_dataset, $this->getConnection()->createDataSet());
    }

    public function testNewAccountCreation()
    {
        $bank_account = new BankAccount('12345678912345678', $this->pdo);

        $xml_dataset = $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/bank-account-after-new-account.xml');
        $this->assertDataSetsEqual($xml_dataset, $this->getConnection()->createDataSet());
    }
    /*
    */
}
?>
