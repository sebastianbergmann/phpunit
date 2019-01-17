<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

/**
 * Tests for the BankAccount class.
 */
class BankAccountTest extends TestCase
{
    protected $ba;

    protected function setUp(): void
    {
        $this->ba = new BankAccount;
    }

    /**
     * @covers BankAccount::getBalance
     * @group balanceIsInitiallyZero
     * @group specification
     */
    public function testBalanceIsInitiallyZero(): void
    {
        /* @Given a fresh bank account */
        $ba = new BankAccount;

        /* @When I ask it for its balance */
        $balance = $ba->getBalance();

        /* @Then I should get 0 */
        $this->assertEquals(0, $balance);
    }

    /**
     * @covers BankAccount::withdrawMoney
     * @group balanceCannotBecomeNegative
     * @group specification
     */
    public function testBalanceCannotBecomeNegative(): void
    {
        try {
            $this->ba->withdrawMoney(1);
        } catch (BankAccountException $e) {
            $this->assertEquals(0, $this->ba->getBalance());

            return;
        }

        $this->fail();
    }

    /**
     * @covers BankAccount::depositMoney
     * @group balanceCannotBecomeNegative
     * @group specification
     */
    public function testBalanceCannotBecomeNegative2(): void
    {
        try {
            $this->ba->depositMoney(-1);
        } catch (BankAccountException $e) {
            $this->assertEquals(0, $this->ba->getBalance());

            return;
        }

        $this->fail();
    }

    /*
     * @covers BankAccount::getBalance
     * @covers BankAccount::depositMoney
     * @covers BankAccount::withdrawMoney
     * @group balanceCannotBecomeNegative
     */
    /*
    public function testDepositingAndWithdrawingMoneyWorks()
    {
        $this->assertEquals(0, $this->ba->getBalance());
        $this->ba->depositMoney(1);
        $this->assertEquals(1, $this->ba->getBalance());
        $this->ba->withdrawMoney(1);
        $this->assertEquals(0, $this->ba->getBalance());
    }
    */
}
