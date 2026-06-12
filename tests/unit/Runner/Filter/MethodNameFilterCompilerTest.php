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

#[CoversClass(MethodNameFilterCompiler::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/filter')]
final class MethodNameFilterCompilerTest extends TestCase
{
    /**
     * @return non-empty-list<array{non-empty-string, non-empty-string}>
     */
    public static function provideFiltersWithMethodNamePortion(): array
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
    public static function provideFiltersThatYieldNull(): array
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
     * @param non-empty-string $filter
     * @param non-empty-string $expected
     */
    #[DataProvider('provideFiltersWithMethodNamePortion')]
    #[TestDox('Returns substring regex for filter "$filter"')]
    public function testReturnsCompiledRegexWhenMethodNamePortionIsPresent(string $filter, string $expected): void
    {
        $this->assertSame($expected, MethodNameFilterCompiler::compile($filter));
    }

    #[DataProvider('provideFiltersThatYieldNull')]
    #[TestDox('Returns null for filter "$filter"')]
    public function testReturnsNullWhenMethodNamePortionCannotBeDetermined(string $filter): void
    {
        $this->assertNull(MethodNameFilterCompiler::compile($filter));
    }
}
