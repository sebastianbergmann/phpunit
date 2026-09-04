<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use function array_keys;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Filter\CompiledGroupFilter;
use PHPUnit\TestFixture\Success;

#[CoversClass(GroupPruner::class)]
#[UsesClass(TestIndexEntry::class)]
#[UsesClass(CompiledGroupFilter::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class GroupPrunerTest extends TestCase
{
    #[TestDox('Skips nothing when no groups are selected')]
    public function testSkipsNothingWhenNoGroupsAreSelected(): void
    {
        $pruner = new GroupPruner([], []);

        $this->assertFalse($pruner->prunes());
        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['a']])));
    }

    public function testSkipsFileWithoutTestInIncludedGroup(): void
    {
        $pruner = new GroupPruner(['b'], []);

        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => ['a'], 'testTwo' => ['c']])));
    }

    public function testDoesNotSkipFileWithTestInIncludedGroup(): void
    {
        $pruner = new GroupPruner(['b'], []);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['a'], 'testTwo' => ['b']])));
    }

    public function testSkipsFileWhereEveryTestIsInExcludedGroup(): void
    {
        $pruner = new GroupPruner([], ['a']);

        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => ['a'], 'testTwo' => ['a', 'b']])));
    }

    public function testDoesNotSkipFileWithTestOutsideExcludedGroup(): void
    {
        $pruner = new GroupPruner([], ['a']);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['a'], 'testTwo' => ['b']])));
    }

    #[TestDox('Skips a file when a test is in an included group but also in an excluded group')]
    public function testSkipsFileWhenTestIsInIncludedGroupAndInExcludedGroup(): void
    {
        $pruner = new GroupPruner(['a'], ['b']);

        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => ['a', 'b']])));
    }

    #[TestDox('Does not skip a file when a test is in an included group and in no excluded group')]
    public function testDoesNotSkipFileWhenTestIsInIncludedGroupAndInNoExcludedGroup(): void
    {
        $pruner = new GroupPruner(['a'], ['b']);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['a', 'b'], 'testTwo' => ['a']])));
    }

    #[TestDox('Skips a file without a test that is in every group of an included conjunction')]
    public function testSkipsFileWithoutTestInEveryGroupOfIncludedConjunction(): void
    {
        $pruner = new GroupPruner(['a+b'], []);

        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => ['a'], 'testTwo' => ['b']])));
    }

    #[TestDox('Does not skip a file with a test that is in every group of an included conjunction')]
    public function testDoesNotSkipFileWithTestInEveryGroupOfIncludedConjunction(): void
    {
        $pruner = new GroupPruner(['a+b'], []);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['a'], 'testTwo' => ['a', 'b']])));
    }

    #[TestDox('Skips a file where every test is in every group of an excluded conjunction')]
    public function testSkipsFileWhereEveryTestIsInEveryGroupOfExcludedConjunction(): void
    {
        $pruner = new GroupPruner([], ['a+b']);

        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => ['a', 'b'], 'testTwo' => ['a', 'b', 'c']])));
    }

    #[TestDox('Does not skip a file with a test that is in only one group of an excluded conjunction')]
    public function testDoesNotSkipFileWithTestInOnlyOneGroupOfExcludedConjunction(): void
    {
        $pruner = new GroupPruner([], ['a+b']);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['a', 'b'], 'testTwo' => ['a']])));
    }

    #[TestDox('Considers a conjunction of the group configured for a test file and a group of a test')]
    public function testConsidersConjunctionOfGroupFromConfigurationAndGroupOfTest(): void
    {
        $pruner = new GroupPruner(['a+from-configuration'], []);
        $entry  = $this->entry(['testOne' => ['a']]);

        $this->assertTrue($pruner->canSkip($entry));
        $this->assertFalse($pruner->canSkip($entry, ['from-configuration']));
    }

    #[TestDox('Treats a test that is in no group as being in the default group')]
    public function testTreatsTestThatIsInNoGroupAsBeingInDefaultGroup(): void
    {
        $pruner = new GroupPruner(['default'], []);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => []])));
    }

    #[TestDox('Treats a test that is only in virtual groups as being in the default group')]
    public function testTreatsTestThatIsOnlyInVirtualGroupsAsBeingInDefaultGroup(): void
    {
        $pruner = new GroupPruner(['default'], []);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['__phpunit_covers_foo']])));
    }

    #[TestDox('Does not treat a test that is in a group as being in the default group')]
    public function testDoesNotTreatTestThatIsInGroupAsBeingInDefaultGroup(): void
    {
        $pruner = new GroupPruner(['default'], []);

        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => ['a', '__phpunit_covers_foo']])));
    }

    #[TestDox('Selects tests by the virtual group that backs --covers')]
    public function testSelectsTestsByVirtualGroup(): void
    {
        $pruner = new GroupPruner(['__phpunit_covers_foo'], []);

        $this->assertFalse($pruner->canSkip($this->entry(['testOne' => ['__phpunit_covers_foo']])));
        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => ['__phpunit_covers_bar']])));
    }

    #[TestDox('Considers the groups configured for a test file in the XML configuration file')]
    public function testConsidersGroupsFromConfiguration(): void
    {
        $pruner = new GroupPruner(['from-configuration'], []);
        $entry  = $this->entry(['testOne' => ['a']]);

        $this->assertTrue($pruner->canSkip($entry));
        $this->assertFalse($pruner->canSkip($entry, ['from-configuration']));
    }

    #[TestDox('Does not treat a test as being in the default group when the XML configuration file puts it in a group')]
    public function testDoesNotTreatTestAsBeingInDefaultGroupWhenConfigurationPutsItInGroup(): void
    {
        $pruner = new GroupPruner(['default'], []);

        $this->assertTrue($pruner->canSkip($this->entry(['testOne' => []]), ['from-configuration']));
    }

    #[TestDox('Does not skip a test class without test methods, so the warning about it is not lost')]
    public function testDoesNotSkipTestClassWithoutTestMethods(): void
    {
        $pruner = new GroupPruner(['a'], []);

        $this->assertFalse($pruner->canSkip($this->entry([])));
    }

    /**
     * @param array<non-empty-string, list<non-empty-string>> $groups
     */
    private function entry(array $groups): TestIndexEntry
    {
        $dataSets = [];

        foreach (array_keys($groups) as $methodName) {
            $dataSets[$methodName] = false;
        }

        return TestIndexEntry::from(
            Success::class,
            $groups,
            $dataSets,
            false,
            [__FILE__ => 'irrelevant'],
        );
    }
}
