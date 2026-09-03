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

use PHPUnit\Event\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Event\TestRunner\DeprecationTriggered;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestSuiteSorter;
use ReflectionProperty;

#[CoversClass(ExecutionOrderParser::class)]
#[CoversClass(ExecutionOrder::class)]
#[Medium]
final class ExecutionOrderParserTest extends TestCase
{
    /**
     * @return non-empty-list<array{non-empty-string, int, int, bool}>
     */
    public static function provider(): array
    {
        return [
            'default'             => ['default', TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFAULT, true],
            'defects'             => ['defects', TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFECTS_FIRST, false],
            'depends'             => ['depends', TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFAULT, true],
            'no-depends'          => ['no-depends', TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFAULT, false],
            'random'              => ['random', TestSuiteSorter::ORDER_RANDOMIZED, TestSuiteSorter::ORDER_DEFAULT, false],
            'reverse'             => ['reverse', TestSuiteSorter::ORDER_REVERSED, TestSuiteSorter::ORDER_DEFAULT, false],
            'duration-ascending'  => ['duration-ascending', TestSuiteSorter::ORDER_DURATION_ASCENDING, TestSuiteSorter::ORDER_DEFAULT, false],
            'duration-descending' => ['duration-descending', TestSuiteSorter::ORDER_DURATION_DESCENDING, TestSuiteSorter::ORDER_DEFAULT, false],
            'size-ascending'      => ['size-ascending', TestSuiteSorter::ORDER_SIZE_ASCENDING, TestSuiteSorter::ORDER_DEFAULT, false],
            'size-descending'     => ['size-descending', TestSuiteSorter::ORDER_SIZE_DESCENDING, TestSuiteSorter::ORDER_DEFAULT, false],

            'main order and defects'          => ['size-ascending,defects', TestSuiteSorter::ORDER_SIZE_ASCENDING, TestSuiteSorter::ORDER_DEFECTS_FIRST, false],
            'dependencies and main order'     => ['depends,reverse', TestSuiteSorter::ORDER_REVERSED, TestSuiteSorter::ORDER_DEFAULT, true],
            'dependencies, order, defects'    => ['depends,duration-ascending,defects', TestSuiteSorter::ORDER_DURATION_ASCENDING, TestSuiteSorter::ORDER_DEFECTS_FIRST, true],
            'no dependencies, order, defects' => ['no-depends,random,defects', TestSuiteSorter::ORDER_RANDOMIZED, TestSuiteSorter::ORDER_DEFECTS_FIRST, false],
        ];
    }

    #[DataProvider('provider')]
    public function testResolvesTokensToConfiguration(string $value, int $expectedOrder, int $expectedOrderDefects, bool $expectedResolveDependencies): void
    {
        $result = $this->parse($value);

        $this->assertSame($expectedOrder, $result->executionOrder());
        $this->assertSame($expectedOrderDefects, $result->executionOrderDefects());
        $this->assertSame($expectedResolveDependencies, $result->resolveDependencies());
        $this->assertSame([], $result->unknownTokens());
    }

    public function testCollectsUnknownTokensInsteadOfIgnoringThem(): void
    {
        $result = $this->parse('reverse,does-not-exist,also-does-not-exist');

        $this->assertSame(['does-not-exist', 'also-does-not-exist'], $result->unknownTokens());
        $this->assertSame(TestSuiteSorter::ORDER_REVERSED, $result->executionOrder());
    }

    #[TestDox('The "default" token resets every knob')]
    public function testDefaultTokenResetsEveryKnob(): void
    {
        $result = $this->parse('no-depends,defects,size-descending,default');

        $this->assertSame(TestSuiteSorter::ORDER_DEFAULT, $result->executionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_DEFAULT, $result->executionOrderDefects());
        $this->assertTrue($result->resolveDependencies());
    }

    public function testDeprecatesRenamedDurationToken(): void
    {
        $this->assertSame(
            ['Using "duration" for --order-by is deprecated and will be removed in PHPUnit 14. Use "duration-ascending" instead.'],
            $this->deprecationsTriggeredBy('duration'),
        );
    }

    public function testDeprecatesRenamedSizeToken(): void
    {
        $this->assertSame(
            ['Using "size" for --order-by is deprecated and will be removed in PHPUnit 14. Use "size-ascending" instead.'],
            $this->deprecationsTriggeredBy('size'),
        );
    }

    public function testDeprecatesMoreThanOneOrder(): void
    {
        $this->assertSame(
            ['Using more than one order for --order-by is deprecated and will be an error in PHPUnit 14. "duration-descending" overrides "duration-ascending".'],
            $this->deprecationsTriggeredBy('duration-ascending,duration-descending'),
        );
    }

    #[TestDox('Deprecates "depends" and "no-depends" being used together')]
    public function testDeprecatesDependsAndNoDependsBeingUsedTogether(): void
    {
        $this->assertSame(
            ['Using both "depends" and "no-depends" for --order-by is deprecated and will be an error in PHPUnit 14.'],
            $this->deprecationsTriggeredBy('depends,no-depends'),
        );
    }

    #[TestDox('Deprecates "defects" being written before the order')]
    public function testDeprecatesDefectsBeingWrittenBeforeTheOrder(): void
    {
        $this->assertSame(
            ['Using "defects" before "duration-ascending" for --order-by is deprecated and will change meaning in PHPUnit 14, where tests are reordered in the order in which the tokens are written. Use "duration-ascending,defects" instead.'],
            $this->deprecationsTriggeredBy('defects,duration-ascending'),
        );
    }

    #[TestDox('Never suggests a deprecated token as the replacement')]
    public function testNeverSuggestsADeprecatedTokenAsTheReplacement(): void
    {
        $this->assertSame(
            [
                'Using "defects" before "size" for --order-by is deprecated and will change meaning in PHPUnit 14, where tests are reordered in the order in which the tokens are written. Use "size-ascending,defects" instead.',
                'Using "size" for --order-by is deprecated and will be removed in PHPUnit 14. Use "size-ascending" instead.',
            ],
            $this->deprecationsTriggeredBy('defects,size'),
        );

        $this->assertSame(
            [
                'Using "defects" before "duration" for --order-by is deprecated and will change meaning in PHPUnit 14, where tests are reordered in the order in which the tokens are written. Use "duration-ascending,defects" instead.',
                'Using "duration" for --order-by is deprecated and will be removed in PHPUnit 14. Use "duration-ascending" instead.',
            ],
            $this->deprecationsTriggeredBy('defects,duration'),
        );
    }

    #[TestDox('Does not deprecate "defects" being written after the order')]
    public function testDoesNotDeprecateDefectsBeingWrittenAfterTheOrder(): void
    {
        $this->assertSame([], $this->deprecationsTriggeredBy('duration-ascending,defects'));
    }

    #[TestDox('Names the configuration surface the value came from')]
    public function testNamesTheConfigurationSurfaceTheValueCameFrom(): void
    {
        $this->assertSame(
            ['Using "size" for the executionOrder attribute is deprecated and will be removed in PHPUnit 14. Use "size-ascending" instead.'],
            $this->deprecationsTriggeredBy('size', 'the executionOrder attribute'),
        );
    }

    /**
     * @param non-empty-string $subject
     */
    private function parse(string $value, string $subject = '--order-by'): ExecutionOrder
    {
        $result = null;

        $this->withThrowAwayEventFacade(
            static function () use (&$result, $value, $subject): void
            {
                $result = (new ExecutionOrderParser)->parse(
                    $value,
                    $subject,
                    TestSuiteSorter::ORDER_DEFAULT,
                    TestSuiteSorter::ORDER_DEFAULT,
                    false,
                );
            },
        );

        $this->assertInstanceOf(ExecutionOrder::class, $result);

        return $result;
    }

    /**
     * @param non-empty-string $subject
     *
     * @return list<string>
     */
    private function deprecationsTriggeredBy(string $value, string $subject = '--order-by'): array
    {
        $tracer = new class implements Tracer
        {
            /**
             * @var list<string>
             */
            public array $messages = [];

            public function trace(Event $event): void
            {
                if ($event instanceof DeprecationTriggered) {
                    $this->messages[] = $event->message();
                }
            }
        };

        $this->withThrowAwayEventFacade(
            static function () use ($value, $subject): void
            {
                (new ExecutionOrderParser)->parse(
                    $value,
                    $subject,
                    TestSuiteSorter::ORDER_DEFAULT,
                    TestSuiteSorter::ORDER_DEFAULT,
                    false,
                );
            },
            $tracer,
        );

        return $tracer->messages;
    }

    /**
     * The parser emits PHPUnit deprecations. These must not end up in the
     * result of the test run that exercises the parser, so they are emitted
     * into a throw-away event facade.
     */
    private function withThrowAwayEventFacade(callable $callable, ?Tracer $tracer = null): void
    {
        $facade = new Facade;

        if ($tracer !== null) {
            $facade->registerTracer($tracer);
        }

        $facade->seal();

        $property = new ReflectionProperty(Facade::class, 'instance');
        $instance = $property->getValue();

        $property->setValue(null, $facade);

        try {
            $callable();
        } finally {
            $property->setValue(null, $instance);
        }
    }
}
