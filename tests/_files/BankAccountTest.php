<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversFunction(BankAccount::class)]
final class BankAccountTest extends TestCase
{
    #[Group('balanceIsInitiallyZero')]
    #[Group('specification')]
    #[Group('1234')]
    public function testBalanceIsInitiallyZero(): void
    {
        /* @Given a fresh bank account */
        $ba = new BankAccount;

        /* @When I ask it for its balance */
        $balance = $ba->getBalance();

        /* @Then I should get 0 */
        $this->assertEquals(0, $balance);
    }

    #[Group('balanceCannotBecomeNegative')]
    #[Group('specification')]
    public function testBalanceCannotBecomeNegative(): void
    {
        $ba = new BankAccount;

        try {
            $ba->withdrawMoney(1);
        } catch (BankAccountException) {
            $this->assertEquals(0, $ba->getBalance());

            return;
        }

        $this->fail();
    }

    #[Group('balanceCannotBecomeNegative')]
    #[Group('specification')]
    public function testBalanceCannotBecomeNegative2(): void
    {
        $ba = new BankAccount;

        try {
            $ba->depositMoney(-1);
        } catch (BankAccountException) {
            $this->assertEquals(0, $ba->getBalance());

            return;
        }

        $this->fail();
    }
}
