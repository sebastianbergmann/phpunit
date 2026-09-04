<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ExecutionOrder\Order;

#[CoversClass(ExecutionOrderParser::class)]
#[CoversClass(ExecutionOrderSource::class)]
#[CoversClass(InvalidExecutionOrderException::class)]
#[UsesClass(Order::class)]
#[Small]
final class ExecutionOrderParserTest extends TestCase
{
    /**
     * @return non-empty-list<array{non-empty-string, list<Order>}>
     */
    public static function provider(): array
    {
        return [
            'default'             => ['default', []],
            'defects'             => ['defects', [Order::Defects]],
            'random'              => ['random', [Order::Random]],
            'reverse'             => ['reverse', [Order::Reverse]],
            'duration-ascending'  => ['duration-ascending', [Order::DurationAscending]],
            'duration-descending' => ['duration-descending', [Order::DurationDescending]],
            'modified-ascending'  => ['modified-ascending', [Order::ModifiedAscending]],
            'modified-descending' => ['modified-descending', [Order::ModifiedDescending]],
            'size-ascending'      => ['size-ascending', [Order::SizeAscending]],
            'size-descending'     => ['size-descending', [Order::SizeDescending]],

            'order then defects' => ['size-ascending,defects', [Order::SizeAscending, Order::Defects]],
            'defects then order' => ['defects,size-ascending', [Order::Defects, Order::SizeAscending]],

            'default resets what precedes it'     => ['random,defects,default', []],
            'default does not reset what follows' => ['random,default,reverse', [Order::Reverse]],
        ];
    }

    /**
     * @param non-empty-string $value
     * @param list<Order>      $expected
     */
    #[DataProvider('provider')]
    public function testResolvesTokensToOrder(string $value, array $expected): void
    {
        $this->assertSame($expected, $this->parse($value));
    }

    #[TestDox('Tokens are applied in the order in which they are written')]
    public function testTokensAreAppliedInTheOrderInWhichTheyAreWritten(): void
    {
        $this->assertSame(
            [Order::DurationAscending, Order::Defects],
            $this->parse('duration-ascending,defects'),
        );

        $this->assertSame(
            [Order::Defects, Order::DurationAscending],
            $this->parse('defects,duration-ascending'),
        );
    }

    public function testRejectsMoreThanOneOrder(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('Cannot use more than one order for --order-by: "duration-ascending" and "duration-descending"');

        $this->parse('duration-ascending,duration-descending');
    }

    public function testRejectsTheSameTokenTwice(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('Cannot use "defects" more than once for --order-by');

        $this->parse('defects,defects');
    }

    public function testRejectsUnknownToken(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('Unknown value "does-not-exist" for --order-by');

        $this->parse('does-not-exist');
    }

    public function testRejectsEmptyToken(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('Unknown value "" for --order-by');

        $this->parse('reverse,');
    }

    #[TestDox('Explains where dependency resolution is configured instead of "depends"')]
    public function testExplainsWhereDependencyResolutionIsConfiguredInsteadOfDepends(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('"depends" is no longer supported for --order-by, use the --resolve-dependencies CLI option instead');

        $this->parse('depends');
    }

    #[TestDox('Explains where dependency resolution is configured in the XML configuration file')]
    public function testExplainsWhereDependencyResolutionIsConfiguredInTheXmlConfigurationFile(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('"depends" is no longer supported for the executionOrder attribute, use the resolveDependencies="true" XML configuration attribute instead');

        $this->parse('depends', ExecutionOrderSource::XmlAttribute);
    }

    #[TestDox('Explains where dependency resolution is configured instead of "no-depends"')]
    public function testExplainsWhereDependencyResolutionIsConfiguredInsteadOfNoDepends(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('"no-depends" is no longer supported for the executionOrder attribute, use the resolveDependencies="false" XML configuration attribute instead');

        $this->parse('no-depends', ExecutionOrderSource::XmlAttribute);
    }

    #[TestDox('Explains how to skip dependency resolution on the command line')]
    public function testExplainsHowToSkipDependencyResolutionOnTheCommandLine(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('"no-depends" is no longer supported for --order-by, use the --ignore-dependencies CLI option instead');

        $this->parse('no-depends');
    }

    #[TestDox('Explains what replaces the removed "duration" token')]
    public function testExplainsWhatReplacesTheRemovedDurationToken(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('"duration" is no longer supported for --order-by, use "duration-ascending" instead');

        $this->parse('duration');
    }

    #[TestDox('Explains what replaces the removed "size" token')]
    public function testExplainsWhatReplacesTheRemovedSizeToken(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('"size" is no longer supported for the executionOrder attribute, use "size-ascending" instead');

        $this->parse('size', ExecutionOrderSource::XmlAttribute);
    }

    #[TestDox('Names the configuration surface the value came from')]
    public function testNamesTheConfigurationSurfaceTheValueCameFrom(): void
    {
        $this->expectException(InvalidExecutionOrderException::class);
        $this->expectExceptionMessageIsOrContains('Unknown value "does-not-exist" for the executionOrder attribute');

        $this->parse('does-not-exist', ExecutionOrderSource::XmlAttribute);
    }

    /**
     * @return list<Order>
     */
    private function parse(string $value, ExecutionOrderSource $source = ExecutionOrderSource::CommandLineOption): array
    {
        return (new ExecutionOrderParser)->parse($value, $source);
    }
}
