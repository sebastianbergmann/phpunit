<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: BankAccount.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

/**
 * A Bank Account.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.3.0
 */
class BankAccount {
    /**
     * The bank account's balance.
     *
     * @var    float
     * @access private
     */
    private $balance = 0;

    /**
     * Returns the bank account's balance.
     *
     * @return float
     * @access public
     */
    public function getBalance() {
        return $this->balance;
    }

    /**
     * Sets the bank account's balance.
     *
     * @param  float $balance
     * @throws Exception
     * @access public
     */
    public function setBalance($balance) {
        if ($balance >= 0) {
            $this->balance = $balance;
        } else {
            throw new Exception;
        }
    }

    /**
     * Deposits an amount of money to the bank account.
     *
     * @param  float $balance
     * @throws Exception
     * @access public
     */
    public function depositMoney($amount) {
        if ($amount >= 0) {
            $this->balance += $amount;
        } else {
            throw new Exception;
        }
    }

    /**
     * Withdraws an amount of money from the bank account.
     *
     * @param  float $balance
     * @throws Exception
     * @access public
     */
    public function withdrawMoney($amount) {
        if ($amount >= 0 && $this->balance >= $amount) {
            $this->balance -= $amount;
        } else {
            throw new Exception;
        }
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
