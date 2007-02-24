<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    CVS: $Id: Money.php,v 1.2.2.2 2005/12/17 16:04:56 sebastian Exp $
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
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
