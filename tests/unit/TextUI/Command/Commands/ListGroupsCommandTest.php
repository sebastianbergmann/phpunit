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

#[CoversClass(ListGroupsCommand::class)]
#[Small]
#[Group('textui')]
#[Group('textui/commands')]
final class ListGroupsCommandTest extends TestCase
{
    public function testListsGroupOfSingleTest(): void
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');
        $test->setGroups(['money']);

        $result = new ListGroupsCommand([$test])->execute();

        $this->assertSame(
            'Available test group:' . PHP_EOL .
            ' - money (1 test)' . PHP_EOL,
            $result->output(),
        );
    }

    public function testCountsTestsPerGroup(): void
    {
        $first = new BankAccountTest('testBalanceIsInitiallyZero');
        $first->setGroups(['money']);

        $second = new BankAccountTest('testBalanceCannotBecomeNegative');
        $second->setGroups(['money', 'bank']);

        $result = new ListGroupsCommand([$first, $second])->execute();

        $this->assertSame(
            'Available test groups:' . PHP_EOL .
            ' - bank (1 test)' . PHP_EOL .
            ' - money (2 tests)' . PHP_EOL,
            $result->output(),
        );
    }

    public function testDoesNotListGroupsThatArePhpunitImplementationDetails(): void
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');
        $test->setGroups(['money', '__phpunit_covers_bankaccount']);

        $result = new ListGroupsCommand([$test])->execute();

        $this->assertSame(
            'Available test groups:' . PHP_EOL .
            ' - money (1 test)' . PHP_EOL,
            $result->output(),
        );
    }

    public function testListsDefaultGroupForPhptTest(): void
    {
        $filename = realpath(__DIR__ . '/../../../../end-to-end/_files/phpt-expect-location-hint-example.phpt');

        $result = new ListGroupsCommand([new PhptTestCase($filename)])->execute();

        $this->assertSame(
            'Available test group:' . PHP_EOL .
            ' - default (1 test)' . PHP_EOL,
            $result->output(),
        );
    }
}
