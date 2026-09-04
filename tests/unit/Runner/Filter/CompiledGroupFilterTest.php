<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompiledGroupFilter::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/filter')]
final class CompiledGroupFilterTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: bool, 1: list<non-empty-string>, 2: list<non-empty-string>}>
     */
    public static function provider(): array
    {
        return [
            'no group is selected'                                         => [false, [], ['a']],
            'test is in the selected group'                                => [true, ['a'], ['a']],
            'test is in one of the selected groups'                        => [true, ['a', 'b'], ['b']],
            'test is in none of the selected groups'                       => [false, ['a', 'b'], ['c']],
            'test is in both groups of the conjunction'                    => [true, ['a+b'], ['a', 'b']],
            'test is in more groups than the conjunction'                  => [true, ['a+b'], ['a', 'b', 'c']],
            'test is in only one group of the conjunction'                 => [false, ['a+b'], ['a']],
            'test is in neither group of the conjunction'                  => [false, ['a+b'], ['c']],
            'order of the groups of a conjunction'                         => [true, ['b+a'], ['a', 'b']],
            'test is in all three groups of the conjunction'               => [true, ['a+b+c'], ['a', 'b', 'c']],
            'test is in two of three groups of a conjunction'              => [false, ['a+b+c'], ['a', 'b']],
            'test matches the conjunction of a selection'                  => [true, ['a+b', 'c'], ['a', 'b']],
            'test matches the single group of a selection'                 => [true, ['a+b', 'c'], ['c']],
            'test matches neither part of a selection'                     => [false, ['a+b', 'c'], ['a']],
            'group whose name is a conjunction of one group'               => [true, ['a+a'], ['a']],
            'group whose name begins with the separator'                   => [true, ['+a'], ['+a']],
            'group whose name ends with the separator'                     => [true, ['a+'], ['a+']],
            'group whose name is the separator'                            => [true, ['+'], ['+']],
            'group whose name begins with the separator is no conjunction' => [false, ['+a'], ['a']],
            'group whose name is a number'                                 => [true, ['5'], ['5']],
        ];
    }

    /**
     * @return non-empty-list<array{0: bool, 1: non-empty-string}>
     */
    public static function conjunctionProvider(): array
    {
        return [
            [false, 'a'],
            [true, 'a+b'],
            [true, 'a+b+c'],
            [true, 'a+a'],
            [false, '+a'],
            [false, 'a+'],
            [false, '+'],
            [false, 'a++b'],
        ];
    }

    /**
     * @param list<non-empty-string> $groups
     * @param list<non-empty-string> $groupsOfTest
     */
    #[DataProvider('provider')]
    public function testMatchesTestsThatAreInTheSelectedGroups(bool $expected, array $groups, array $groupsOfTest): void
    {
        $this->assertSame($expected, CompiledGroupFilter::from($groups)->matches($groupsOfTest));
    }

    public function testIsEmptyWhenNoGroupIsSelected(): void
    {
        $this->assertTrue(CompiledGroupFilter::from([])->isEmpty());
    }

    public function testIsNotEmptyWhenAGroupIsSelected(): void
    {
        $this->assertFalse(CompiledGroupFilter::from(['a'])->isEmpty());
    }

    public function testMentionsTheGroupsThatAreSelected(): void
    {
        $filter = CompiledGroupFilter::from(['a', 'b+c']);

        $this->assertTrue($filter->mentions('a'));
        $this->assertTrue($filter->mentions('b'));
        $this->assertTrue($filter->mentions('c'));
    }

    public function testDoesNotMentionAGroupThatIsNotSelected(): void
    {
        $this->assertFalse(CompiledGroupFilter::from(['a', 'b+c'])->mentions('d'));
    }

    #[TestDox('Does not mention the name of a conjunction as the name of a group')]
    public function testDoesNotMentionTheNameOfAConjunction(): void
    {
        $this->assertFalse(CompiledGroupFilter::from(['b+c'])->mentions('b+c'));
    }

    /**
     * @param non-empty-string $group
     */
    #[DataProvider('conjunctionProvider')]
    public function testKnowsWhetherAGroupNameIsParsedAsAConjunction(bool $expected, string $group): void
    {
        $this->assertSame($expected, CompiledGroupFilter::isConjunction($group));
    }
}
