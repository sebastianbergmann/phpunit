<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\AssertionExampleTest;
use PHPUnit\TestFixture\BankAccountTest;
use PHPUnit\TestFixture\NumericGroupAnnotationTest;

#[CoversClass(Groups::class)]
#[Small]
final class GroupsTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                AssertionExampleTest::class,
                'testOne',
                [
                    'default',
                ],
            ],

            [
                BankAccountTest::class,
                'testBalanceIsInitiallyZero',
                [
                    'balanceIsInitiallyZero',
                    'specification',
                    '1234',
                    '__phpunit_covers_bankaccount::getbalance',
                ],
            ],

            [
                NumericGroupAnnotationTest::class,
                'testTicketAnnotationSupportsNumericValue',
                [
                    't123456',
                    '3502',
                ],
            ],

            [
                NumericGroupAnnotationTest::class,
                'testGroupAnnotationSupportsNumericValue',
                [
                    't123456',
                    '3502',
                ],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testGroupsAreAssigned(string $class, string $method, array $groups): void
    {
        $this->assertSame($groups, (new Groups)->groups($class, $method));
    }
}
