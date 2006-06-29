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
 * @version    CVS: $Id: Money.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'IMoney.php';

/**
 * A Money.
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
class Money implements IMoney {
    private $fAmount;
    private $fCurrency;

    public function __construct($amount, $currency) {
        $this->fAmount   = $amount;
        $this->fCurrency = $currency;
    }

    public function add(IMoney $m) {
        return $m->addMoney($this);
    }

    public function addMoney(Money $m) {
        if ($this->currency() == $m->currency()) {
            return new Money($this->amount() + $m->amount(), $this->currency());
        }

        return MoneyBag::create($this, $m);
    }

    public function addMoneyBag(MoneyBag $s) {
        return $s->addMoney($this);
    }

    public function amount() {
        return $this->fAmount;
    }

    public function currency() {
        return $this->fCurrency;
    }

    public function equals($anObject) {
        if ($this->isZero() &&
            $anObject instanceof IMoney) {
            return $anObject->isZero();
        }

        if ($anObject instanceof Money) {
            return ($this->currency() == $anObject->currency() &&
                    $this->amount() == $anObject->amount());
        }

        return FALSE;
    }

    public function hashCode() {
        return crc32($this->fCurrency) + $this->fAmount;
    }

    public function isZero() {
        return $this->amount() == 0;
    }

    public function multiply($factor) {
        return new Money($this->amount() * $factor, $this->currency());
    }

    public function negate() {
        return new Money(-1 * $this->amount(), $this->currency());
    }

    public function subtract(IMoney $m) {
        return $this->add($m->negate());
    }

    public function toString() {
        return '[' . $this->amount() . ' ' . $this->currency() . ']';
    }

    public function appendTo(MoneyBag $m) {
        $m->appendMoney($this);
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
