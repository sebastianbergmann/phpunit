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
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'Money.php';
require_once 'MoneyBag.php';

/**
 * Tests for the Money and MoneyBag classes.
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
class MoneyTest extends PHPUnit2_Framework_TestCase {
    private $f12EUR;
    private $f14EUR;
    private $f7USD;
    private $f21USD;

    private $fMB1;
    private $fMB2;
    
    protected function setUp() {
        $this->f12EUR = new Money(12, 'EUR');
        $this->f14EUR = new Money(14, 'EUR');
        $this->f7USD  = new Money( 7, 'USD');
        $this->f21USD = new Money(21, 'USD');

        $this->fMB1 = MoneyBag::create($this->f12EUR, $this->f7USD);
        $this->fMB2 = MoneyBag::create($this->f14EUR, $this->f21USD);
    }

    public function testBagMultiply() {
        // {[12 EUR][7 USD]} *2 == {[24 EUR][14 USD]}
        $expected = MoneyBag::create(new Money(24, 'EUR'), new Money(14, 'USD'));

        self::assertTrue($expected->equals($this->fMB1->multiply(2)));
        self::assertTrue($this->fMB1->equals($this->fMB1->multiply(1)));
        self::assertTrue($this->fMB1->multiply(0)->isZero());
    }

    public function testBagNegate() {
        // {[12 EUR][7 USD]} negate == {[-12 EUR][-7 USD]}
        $expected = MoneyBag::create(new Money(-12, 'EUR'), new Money(-7, 'USD'));
        self::assertTrue($expected->equals($this->fMB1->negate()));
    }

    public function testBagSimpleAdd() {
        // {[12 EUR][7 USD]} + [14 EUR] == {[26 EUR][7 USD]}
        $expected = MoneyBag::create(new Money(26, 'EUR'), new Money(7, 'USD'));
        self::assertTrue($expected->equals($this->fMB1->add($this->f14EUR)));
    }

    public function testBagSubtract() {
        // {[12 EUR][7 USD]} - {[14 EUR][21 USD] == {[-2 EUR][-14 USD]}
        $expected = MoneyBag::create(new Money(-2, 'EUR'), new Money(-14, 'USD'));
        self::assertTrue($expected->equals($this->fMB1->subtract($this->fMB2)));
    }

    public function testBagSumAdd() {
        // {[12 EUR][7 USD]} + {[14 EUR][21 USD]} == {[26 EUR][28 USD]}
        $expected = MoneyBag::create(new Money(26, 'EUR'), new Money(28, 'USD'));
        self::assertTrue($expected->equals($this->fMB1->add($this->fMB2)));
    }

    public function testIsZero() {
        //self::assertTrue($this->fMB1->subtract($this->fMB1)->isZero()); 
        self::assertTrue(MoneyBag::create(new Money (0, 'EUR'), new Money (0, 'USD'))->isZero());
    }

    public function testMixedSimpleAdd() {
        // [12 EUR] + [7 USD] == {[12 EUR][7 USD]}
        $expected = MoneyBag::create($this->f12EUR, $this->f7USD);
        self::assertTrue($expected->equals($this->f12EUR->add($this->f7USD)));
    }

    public function testBagNotEquals() {
        $bag1 = MoneyBag::create($this->f12EUR, $this->f7USD);
        $bag2 = new Money(12, 'CHF');
        $bag2->add($this->f7USD);
        self::assertFalse($bag1->equals($bag2));
    }

    public function testMoneyBagEquals() {
        self::assertTrue(!$this->fMB1->equals(NULL)); 
        
        self::assertTrue($this->fMB1->equals($this->fMB1));
        $equal = MoneyBag::create(new Money(12, 'EUR'), new Money(7, 'USD'));
        self::assertTrue($this->fMB1->equals($equal));
        self::assertTrue(!$this->fMB1->equals($this->f12EUR));
        self::assertTrue(!$this->f12EUR->equals($this->fMB1));
        self::assertTrue(!$this->fMB1->equals($this->fMB2));
    }

    public function testMoneyBagHash() {
        $equal = MoneyBag::create(new Money(12, 'EUR'), new Money(7, 'USD'));
        self::assertEquals($this->fMB1->hashCode(), $equal->hashCode());
    }

    public function testMoneyEquals() {
        self::assertTrue(!$this->f12EUR->equals(NULL)); 
        $equalMoney = new Money(12, 'EUR');
        self::assertTrue($this->f12EUR->equals($this->f12EUR));
        self::assertTrue($this->f12EUR->equals($equalMoney));
        self::assertEquals($this->f12EUR->hashCode(), $equalMoney->hashCode());
        self::assertFalse($this->f12EUR->equals($this->f14EUR));
    }

    public function testMoneyHash() {
        self::assertNotNull($this->f12EUR);
        $equal= new Money(12, 'EUR');
        self::assertEquals($this->f12EUR->hashCode(), $equal->hashCode());
    }

    public function testSimplify() {
        $money = MoneyBag::create(new Money(26, 'EUR'), new Money(28, 'EUR'));
        self::assertTrue($money->equals(new Money(54, 'EUR')));
    }

    public function testNormalize2() {
        // {[12 EUR][7 USD]} - [12 EUR] == [7 USD]
        $expected = new Money(7, 'USD');
        self::assertTrue($expected->equals($this->fMB1->subtract($this->f12EUR)));
    }

    public function testNormalize3() {
        // {[12 EUR][7 USD]} - {[12 EUR][3 USD]} == [4 USD]
        $ms1 = MoneyBag::create(new Money(12, 'EUR'), new Money(3, 'USD'));
        $expected = new Money(4, 'USD');
        self::assertTrue($expected->equals($this->fMB1->subtract($ms1)));
    }

    public function testNormalize4() {
        // [12 EUR] - {[12 EUR][3 USD]} == [-3 USD]
        $ms1 = MoneyBag::create(new Money(12, 'EUR'), new Money(3, 'USD'));
        $expected = new Money(-3, 'USD');
        self::assertTrue($expected->equals($this->f12EUR->subtract($ms1)));
    }

    public function testPrint() {
        self::assertEquals('[12 EUR]', $this->f12EUR->toString());
    }

    public function testSimpleAdd() {
        // [12 EUR] + [14 EUR] == [26 EUR]
        $expected = new Money(26, 'EUR');
        self::assertTrue($expected->equals($this->f12EUR->add($this->f14EUR)));
    }

    public function testSimpleBagAdd() {
        // [14 EUR] + {[12 EUR][7 USD]} == {[26 EUR][7 USD]}
        $expected = MoneyBag::create(new Money(26, 'EUR'), new Money(7, 'USD'));
        self::assertTrue($expected->equals($this->f14EUR->add($this->fMB1)));
    }

    public function testSimpleMultiply() {
        // [14 EUR] *2 == [28 EUR]
        $expected = new Money(28, 'EUR');
        self::assertTrue($expected->equals($this->f14EUR->multiply(2)));
    }

    public function testSimpleNegate() {
        // [14 EUR] negate == [-14 EUR]
        $expected = new Money(-14, 'EUR');
        self::assertTrue($expected->equals($this->f14EUR->negate()));
    }

    public function testSimpleSubtract() {
        // [14 EUR] - [12 EUR] == [2 EUR]
        $expected = new Money(2, 'EUR');
        self::assertTrue($expected->equals($this->f14EUR->subtract($this->f12EUR)));
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
