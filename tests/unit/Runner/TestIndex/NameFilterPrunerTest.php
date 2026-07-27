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

use function array_fill_keys;
use function array_keys;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Success;

#[CoversClass(NameFilterPruner::class)]
#[UsesClass(TestIndexEntry::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class NameFilterPrunerTest extends TestCase
{
    /**
     * @return non-empty-list<array{non-empty-string}>
     */
    public static function provideFiltersThatDoNotMatch(): array
    {
        return [
            'method name'              => ['testSomethingElse'],
            'class name'               => ['SomeOtherTest'],
            'fully qualified name'     => ['PHPUnit\\\\TestFixture\\\\Other::testOne'],
            'regular expression'       => ['/testSomethingElse/'],
            'filter with a data set'   => ['testOne#3'],
            'filter with a named set'  => ['testOne@named'],
            'not a regular expression' => ['/unterminated'],
        ];
    }

    #[TestDox('Skips nothing when no filter is used')]
    public function testSkipsNothingWhenNoFilterIsUsed(): void
    {
        $pruner = NameFilterPruner::withoutFilter();

        $this->assertFalse($pruner->prunes());
        $this->assertFalse($pruner->canSkip($this->entry(['testOne'])));
    }

    #[DataProvider('provideFiltersThatDoNotMatch')]
    #[TestDox('Skips a file when no test in it can match filter "$filter"')]
    public function testSkipsFileWhenNoTestInItCanMatchFilter(string $filter): void
    {
        $this->assertTrue(NameFilterPruner::fromFilter($filter)->canSkip($this->entry(['testOne', 'testTwo'])));
    }

    #[TestDox('Does not skip a file when the name of a test in it matches')]
    public function testDoesNotSkipFileWhenNameOfTestInItMatches(): void
    {
        $this->assertFalse(NameFilterPruner::fromFilter('testTwo')->canSkip($this->entry(['testOne', 'testTwo'])));
    }

    #[TestDox('Does not skip a file when the name of its test class matches')]
    public function testDoesNotSkipFileWhenNameOfItsTestClassMatches(): void
    {
        $this->assertFalse(NameFilterPruner::fromFilter('Success')->canSkip($this->entry(['testOne'])));
    }

    #[TestDox('Matches the filter against the fully qualified name of a test')]
    public function testMatchesFilterAgainstFullyQualifiedNameOfTest(): void
    {
        $this->assertFalse(NameFilterPruner::fromFilter(Success::class . '::testOne')->canSkip($this->entry(['testOne'])));
    }

    #[TestDox('Does not skip a file with a test that has data sets, whatever the filter is')]
    public function testDoesNotSkipFileWithTestThatHasDataSets(): void
    {
        $entry = TestIndexEntry::from(
            Success::class,
            ['testOne' => [], 'testTwo' => []],
            ['testOne' => false, 'testTwo' => true],
            [__FILE__  => 'irrelevant'],
        );

        $this->assertFalse(NameFilterPruner::fromFilter('testSomethingElse')->canSkip($entry));
    }

    #[TestDox('Does not skip a test class without test methods, so the warning about it is not lost')]
    public function testDoesNotSkipTestClassWithoutTestMethods(): void
    {
        $this->assertFalse(NameFilterPruner::fromFilter('testSomethingElse')->canSkip($this->entry([])));
    }

    /**
     * A fully qualified name whose backslashes are not escaped compiles to a
     * regular expression that cannot be matched. NameFilterIterator does not
     * select a test in that case either, so the file could be skipped, but
     * keeping it is the safe answer to a question that cannot be answered.
     */
    #[TestDox('Does not skip a file when the filter cannot be matched at all')]
    public function testDoesNotSkipFileWhenFilterCannotBeMatchedAtAll(): void
    {
        $this->assertFalse(NameFilterPruner::fromFilter('PHPUnit\\TestFixture\\Other::testOne')->canSkip($this->entry(['testOne'])));
    }

    /**
     * @param list<non-empty-string> $methodNames
     */
    private function entry(array $methodNames): TestIndexEntry
    {
        $groups = array_fill_keys($methodNames, []);

        return TestIndexEntry::from(
            Success::class,
            $groups,
            array_fill_keys(array_keys($groups), false),
            [__FILE__ => 'irrelevant'],
        );
    }
}
