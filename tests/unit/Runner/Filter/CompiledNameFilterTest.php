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

#[CoversClass(CompiledNameFilter::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/filter')]
final class CompiledNameFilterTest extends TestCase
{
    /**
     * @return non-empty-list<array{string, non-empty-string}>
     */
    public static function provideFiltersAndRegularExpressions(): array
    {
        return [
            'empty string'                     => ['', '{}i'],
            'method name'                      => ['testFoo', '{testFoo}i'],
            'fully qualified method name'      => ['PHPUnit\\TestFixture\\FooTest::testFoo', '{PHPUnit\\TestFixture\\FooTest::testFoo}i'],
            'with numeric data set index'      => ['testFoo#0', '{testFoo.*with data set #0$}i'],
            'with numeric data set range'      => ['testFoo#0-3', '{testFoo.*with data set #(\d+)$}i'],
            'with descending data set range'   => ['testFoo#3-0', '{testFoo.*with data set #3$}i'],
            'with named data set'              => ['testFoo#named', '{testFoo.*with data set "named"$}i'],
            'with attribute constant'          => ['testFoo@CONST', '{testFoo.*with data set "CONST"$}i'],
            'numeric data set index only'      => ['#0', '{#0}i'],
            'numeric data set range only'      => ['#0-3', '{.*with data set #(\d+)$}i'],
            'regular expression is used as-is' => ['/testFoo/i', '/testFoo/i'],
            'regular expression with braces'   => ['{testFoo}', '{testFoo}'],
        ];
    }

    /**
     * @return non-empty-list<array{non-empty-string, non-empty-string}>
     */
    public static function provideFiltersThatConstrainTheMethodName(): array
    {
        return [
            'with numeric data set index'                                  => ['testFoo#0', '{testFoo}i'],
            'with numeric data set range'                                  => ['testFoo#0-3', '{testFoo}i'],
            'with named data set'                                          => ['testFoo#named', '{testFoo}i'],
            'with attribute constant'                                      => ['testFoo@CONST', '{testFoo}i'],
            'fully qualified method name with numeric data set index'      => ['PHPUnit\\TestFixture\\FooTest::testFoo#0', '{PHPUnit\\TestFixture\\FooTest::testFoo}i'],
            'non-alphanumeric leading character that is not a valid regex' => ['_test#0', '{_test}i'],
        ];
    }

    /**
     * @return non-empty-list<array{string}>
     */
    public static function provideFiltersThatDoNotConstrainTheMethodName(): array
    {
        return [
            'empty string'                                            => [''],
            'plain method name (may match a data set name)'           => ['testFoo'],
            'fully qualified method name (may match a data set name)' => ['PHPUnit\\TestFixture\\FooTest::testFoo'],
            'numeric index only'                                      => ['#0'],
            'numeric range only'                                      => ['#0-3'],
            'named data set only'                                     => ['#named'],
            'attribute constant only'                                 => ['@CONST'],
            'valid regex delimited by slashes'                        => ['/testFoo/'],
            'valid regex delimited by hashes'                         => ['#testFoo#i'],
            'valid regex delimited by braces'                         => ['{testFoo}'],
        ];
    }

    /**
     * @param non-empty-string $expected
     */
    #[DataProvider('provideFiltersAndRegularExpressions')]
    #[TestDox('Compiles filter "$filter" to a regular expression matched against the name of a test')]
    public function testCompilesRegularExpressionThatIsMatchedAgainstNameOfTest(string $filter, string $expected): void
    {
        $this->assertSame($expected, CompiledNameFilter::from($filter)->regularExpression());
    }

    /**
     * @param non-empty-string $filter
     * @param non-empty-string $expected
     */
    #[DataProvider('provideFiltersThatConstrainTheMethodName')]
    #[TestDox('Compiles filter "$filter" to a regular expression matched against the name of a test method')]
    public function testCompilesRegularExpressionThatIsMatchedAgainstNameOfTestMethod(string $filter, string $expected): void
    {
        $filter = CompiledNameFilter::from($filter);

        $this->assertTrue($filter->constrainsMethodName());
        $this->assertSame($expected, $filter->methodNameRegularExpression());
    }

    #[DataProvider('provideFiltersThatDoNotConstrainTheMethodName')]
    #[TestDox('Does not constrain the name of a test method for filter "$filter"')]
    public function testDoesNotConstrainNameOfTestMethodWhenItCannotBeDetermined(string $filter): void
    {
        $this->assertFalse(CompiledNameFilter::from($filter)->constrainsMethodName());
    }

    public function testProvidesDataSetRangeWhenFilterSelectsRangeOfDataSets(): void
    {
        $filter = CompiledNameFilter::from('testFoo#0-3');

        $this->assertTrue($filter->hasDataSetRange());
        $this->assertSame(0, $filter->dataSetMinimum());
        $this->assertSame(3, $filter->dataSetMaximum());
    }

    #[TestDox('Does not provide a data set range when the filter does not select a range of data sets')]
    public function testDoesNotProvideDataSetRangeWhenFilterDoesNotSelectRangeOfDataSets(): void
    {
        $this->assertFalse(CompiledNameFilter::from('testFoo#0')->hasDataSetRange());
    }

    #[TestDox('Does not provide a data set range when the filter selects a descending range of data sets')]
    public function testDoesNotProvideDataSetRangeWhenFilterSelectsDescendingRangeOfDataSets(): void
    {
        $this->assertFalse(CompiledNameFilter::from('testFoo#3-0')->hasDataSetRange());
    }
}
