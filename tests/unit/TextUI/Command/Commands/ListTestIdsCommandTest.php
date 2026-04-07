<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\TestFixture\BankAccountTest;

#[CoversClass(ListTestIdsCommand::class)]
#[Small]
#[Group('textui')]
#[Group('textui/commands')]
final class ListTestIdsCommandTest extends TestCase
{
    public function testListsTestIdForTestWithoutDataSet(): void
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');

        $command = new ListTestIdsCommand([$test]);
        $result  = $command->execute();

        $this->assertSame(
            'PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero' . PHP_EOL,
            $result->output(),
        );
    }

    public function testListsTestIdForTestWithNumericDataSet(): void
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');
        $test->setData(0, [1]);

        $command = new ListTestIdsCommand([$test]);
        $result  = $command->execute();

        $this->assertSame(
            'PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero#0' . PHP_EOL,
            $result->output(),
        );
    }

    public function testListsTestIdForTestWithNamedDataSet(): void
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');
        $test->setData('initial balance', [1]);

        $command = new ListTestIdsCommand([$test]);
        $result  = $command->execute();

        $this->assertSame(
            'PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero#initial balance' . PHP_EOL,
            $result->output(),
        );
    }

    public function testListsTestIdForPhptTest(): void
    {
        $filename = realpath(__DIR__ . '/../../../../end-to-end/_files/phpt-expect-location-hint-example.phpt');

        $test = new PhptTestCase($filename);

        $command = new ListTestIdsCommand([$test]);
        $result  = $command->execute();

        $this->assertSame(
            $filename . PHP_EOL,
            $result->output(),
        );
    }

    public function testListsTestIdsForMultipleTests(): void
    {
        $test1 = new BankAccountTest('testBalanceIsInitiallyZero');
        $test2 = new BankAccountTest('testBalanceCannotBecomeNegative');

        $command = new ListTestIdsCommand([$test1, $test2]);
        $result  = $command->execute();

        $this->assertSame(
            'PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero' . PHP_EOL .
            'PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative' . PHP_EOL,
            $result->output(),
        );
    }

    public function testEmptyOutputForNoTests(): void
    {
        $command = new ListTestIdsCommand([]);
        $result  = $command->execute();

        $this->assertSame('', $result->output());
    }
}
