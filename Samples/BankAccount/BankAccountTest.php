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
 * @version    CVS: $Id: BankAccountTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'BankAccount.php';

/**
 * Tests for the BankAccount class.
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
class BankAccountTest extends PHPUnit2_Framework_TestCase {
    /**
     * BankAccount object used as the tests' fixture.
     *
     * @var    BankAccount
     * @access private
     */
    private $ba;

    /**
     * Sets up the test fixture.
     *
     * @access protected
     */
    protected function setUp() {
        $this->ba = new BankAccount;
    }

    /**
     * Asserts that the balance is initially zero.
     *
     * @access public
     */
    public function testBalanceIsInitiallyZero() {
        $this->assertEquals(0, $this->ba->getBalance());
    }

    /**
     * Asserts that the balance cannot become negative.
     *
     * @access public
     */
    public function testBalanceCannotBecomeNegative() {
        try {
            $this->ba->withdrawMoney(1);
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * Asserts that the balance cannot become negative.
     *
     * @access public
     */
    public function testBalanceCannotBecomeNegative2() {
        try {
            $this->ba->depositMoney(-1);
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    /**
     * Asserts that the balance cannot become negative.
     *
     * @access public
     */
    public function testBalanceCannotBecomeNegative3() {
        try {
            $this->ba->setBalance(-1);
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
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
