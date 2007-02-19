<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

require_once 'IMoney.php';
require_once 'Money.php';

/**
 * A MoneyBag.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.3.0
 */
class MoneyBag implements IMoney
{
    private $fMonies = array();

    public static function create(IMoney $m1, IMoney $m2)
    {
        $result = new MoneyBag;
        $m1->appendTo($result);
        $m2->appendTo($result);

        return $result->simplify();
    }

    public function add(IMoney $m)
    {
        return $m->addMoneyBag($this);
    }

    public function addMoney(Money $m)
    {
        return MoneyBag::create($m, $this);
    }

    public function addMoneyBag(MoneyBag $s)
    {
        return MoneyBag::create($s, $this);
    }

    public function appendBag(MoneyBag $aBag)
    {
        foreach ($aBag->monies() as $aMoney) {
            $this->appendMoney($aMoney);
        }
    }

    public function monies()
    {
        return $this->fMonies;
    }

    public function appendMoney(Money $aMoney)
    {
        if ($aMoney->isZero()) {
            return;
        }

        $old = $this->findMoney($aMoney->currency());

        if ($old == NULL) {
            $this->fMonies[] = $aMoney;
            return;
        }

        $keys = array_keys($this->fMonies);
        $max  = count($keys);

        for ($i = 0; $i < $max; $i++) {
            if ($this->fMonies[$keys[$i]] === $old) {
                unset($this->fMonies[$keys[$i]]);
                break;
            }
        }

        $sum = $old->add($aMoney);

        if ($sum->isZero()) {
            return;
        }

        $this->fMonies[] = $sum;
    }

    public function equals($anObject)
    {
        if ($this->isZero() &&
            $anObject instanceof IMoney) {
            return $anObject->isZero();
        }

        if ($anObject instanceof MoneyBag) {
            if (count($anObject->monies()) != count($this->fMonies)) {
                return FALSE;
            }

            foreach ($this->fMonies as $m) {
                if (!$anObject->contains($m)) {
                    return FALSE;
                }
            }

            return TRUE;
        }

        return FALSE;
    }

    private function findMoney($currency)
    {
        foreach ($this->fMonies as $m) {
            if ($m->currency() == $currency) {
                return $m;
            }
        }

        return NULL;
    }

    private function contains(Money $m)
    {
        $found = $this->findMoney($m->currency());

        if ($found == NULL) {
            return FALSE;
        }

        return $found->amount() == $m->amount();
    }

    public function hashCode()
    {
        $hash = 0;

        foreach ($this->fMonies as $m) {
            $hash ^= $m->hashCode();
        }

        return $hash;
    }

    public function isZero()
    {
        return count($this->fMonies) == 0;
    }

    public function multiply($factor)
    {
        $result = new MoneyBag;

        if ($factor != 0) {
            foreach ($this->fMonies as $m) {
                $result->appendMoney($m->multiply($factor));
            }
        }

        return $result;
    }

    public function negate()
    {
        $result = new MoneyBag;

        foreach ($this->fMonies as $m) {
                $result->appendMoney($m->negate());
        }

        return $result;
    }

    private function simplify()
    {
        if (count($this->fMonies) == 1) {
            return array_pop($this->fMonies);
        }

        return $this;
    }

    public function subtract(IMoney $m)
    {
        return $this->add($m->negate());
    }

    public function toString()
    {
        $buffer = '{';

        foreach ($this->fMonies as $m) {
            $buffer .= $m->toString();
        }

        return $buffer . '}';
    }

    public function appendTo(MoneyBag $m)
    {
        $m->appendBag($this);
    }
}
?>
