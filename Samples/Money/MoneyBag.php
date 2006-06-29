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
 * @version    CVS: $Id: MoneyBag.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'IMoney.php';
require_once 'Money.php';

/**
 * A MoneyBag.
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
class MoneyBag implements IMoney {
    private $fMonies = array();

    public static function create(IMoney $m1, IMoney $m2) {
        $result = new MoneyBag;
        $m1->appendTo($result);
        $m2->appendTo($result);

        return $result->simplify();
    }

    public function add(IMoney $m) {
        return $m->addMoneyBag($this);
    }

    public function addMoney(Money $m) { 
        return MoneyBag::create($m, $this);
    }

    public function addMoneyBag(MoneyBag $s) {
        return MoneyBag::create($s, $this);
    }

    public function appendBag(MoneyBag $aBag) {
        foreach ($aBag->monies() as $aMoney) {
            $this->appendMoney($aMoney);
        }
    }

    public function monies() {
        return $this->fMonies;
    }

    public function appendMoney(Money $aMoney) {
        if ($aMoney->isZero()) {
            return;
        }

        $old = $this->findMoney($aMoney->currency());

        if ($old == NULL) {
            $this->fMonies[] = $aMoney;
            return;
        }

        $keys = array_keys($this->fMonies);

        for ($i = 0; $i < sizeof($keys); $i++) {
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

    public function equals($anObject) {
        if ($this->isZero() &&
            $anObject instanceof IMoney) {
            return $anObject->isZero();
        }

        if ($anObject instanceof MoneyBag) {
            if (sizeof($anObject->monies()) != sizeof($this->fMonies)) {
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

    private function findMoney($currency) {
        foreach ($this->fMonies as $m) {
            if ($m->currency() == $currency) {
                return $m;
            }
        }

        return NULL;
    }

    private function contains(Money $m) {
        $found = $this->findMoney($m->currency());

        if ($found == NULL) {
            return FALSE;
        }

        return $found->amount() == $m->amount();
    }

    public function hashCode() {
        $hash = 0;

        foreach ($this->fMonies as $m) {
            $hash ^= $m->hashCode();
        }

        return $hash;
    }

    public function isZero() {
        return sizeof($this->fMonies) == 0;
    }

    public function multiply($factor) {
        $result = new MoneyBag;

        if ($factor != 0) {
            foreach ($this->fMonies as $m) {
                $result->appendMoney($m->multiply($factor));
            }
        }

        return $result;
    }

    public function negate() {
        $result = new MoneyBag;

        foreach ($this->fMonies as $m) {
                $result->appendMoney($m->negate());
        }

        return $result;
    }

    private function simplify() {
        if (sizeof($this->fMonies) == 1) {
            return array_pop($this->fMonies);
        }

        return $this;
    }

    public function subtract(IMoney $m) {
        return $this->add($m->negate());
    }

    public function toString() {
        $buffer = '{';

        foreach ($this->fMonies as $m) {
            $buffer .= $m->toString();
        }

        return $buffer . '}';
    }

    public function appendTo(MoneyBag $m) {
        $m->appendBag($this);
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
