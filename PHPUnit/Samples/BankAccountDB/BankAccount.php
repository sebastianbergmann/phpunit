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

class BankAccountException extends RuntimeException {}

/**
 * A bank account.
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
class BankAccount
{
    /**
     * The bank account's balance.
     *
     * @var    float
     */
    protected $balance = 0;

    /**
     * The bank account's number.
     *
     * @var    float
     */
    protected $accountNumber = 0;

    /**
     * The PDO connection used to store and retrieve bank account information.
     *
     * @var PDO
     */
    protected $pdo;

    public function __construct($accountNumber, PDO $pdo)
    {
        $this->accountNumber = $accountNumber;
        $this->pdo = $pdo;

        $this->loadAccount();
    }

    /**
     * Returns the bank account's balance.
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Sets the bank account's balance.
     *
     * @param  float $balance
     * @throws BankAccountException
     */
    protected function setBalance($balance)
    {
        if ($balance >= 0) {
            $this->balance = $balance;
            $this->updateAccount();
        } else {
            throw new BankAccountException;
        }
    }

    /**
     * Returns the bank account's number.
     *
     * @return float
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Deposits an amount of money to the bank account.
     *
     * @param  float $balance
     * @throws BankAccountException
     */
    public function depositMoney($balance)
    {
        $this->setBalance($this->getBalance() + $balance);

        return $this->getBalance();
    }

    /**
     * Withdraws an amount of money from the bank account.
     *
     * @param  float $balance
     * @throws BankAccountException
     */
    public function withdrawMoney($balance)
    {
        $this->setBalance($this->getBalance() - $balance);

        return $this->getBalance();
    }

    /**
     * Loads account information from the database.
     */
    protected function loadAccount()
    {
        $query = "SELECT * FROM bank_account WHERE account_number = ?";

        $statement = $this->pdo->prepare($query);

        $statement->execute(array($this->accountNumber));

        if ($bankAccountInfo = $statement->fetch(PDO::FETCH_ASSOC))
        {
            $this->balance = $bankAccountInfo['balance'];
        }
        else
        {
            $this->balance = 0;
            $this->addAccount();
        }
    }

    /**
     * Saves account information to the database.
     */
    protected function updateAccount()
    {
        $query = "UPDATE bank_account SET balance = ? WHERE account_number = ?";

        $statement = $this->pdo->prepare($query);
        $statement->execute(array($this->balance, $this->accountNumber));
    }

    /**
     * Adds account information to the database.
     */
    protected function addAccount()
    {
        $query = "INSERT INTO bank_account (balance, account_number) VALUES(?, ?)";

        $statement = $this->pdo->prepare($query);
        $statement->execute(array($this->balance, $this->accountNumber));
    }

    static public function createTable(PDO $pdo)
    {
        $query = "
            CREATE TABLE bank_account (
                account_number VARCHAR(17) PRIMARY KEY,
                balance DECIMAL(9,2) NOT NULL DEFAULT 0
            );
        ";

        $pdo->query($query);
    }
}
?>