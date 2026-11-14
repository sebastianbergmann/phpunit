<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\StateMachine;

use function array_map;
use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\ForAllExternal;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\InitializeStateMachine;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\InvalidStateMachineException;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\StateMachine\ChildStateMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\ClassWithParentClassThatDoesNotExist;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StateMachineWithClosureThatDoesNotReturnAValueGenerator;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StateMachineWithClosureThatReturnsAnUnsupportedValueGenerator;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StateMachineWithGeneratorSpecifiedWithClosure;
use PHPUnit\TestFixture\PropertyBased\StateMachine\UnsupportedValueGenerator;
use PHPUnit\TestFixture\PropertyBased\StateMachine\ValidStateMachine;

#[CoversClass(StateMachineDefinitionBuilder::class)]
#[CoversClass(StateMachineDefinition::class)]
#[CoversClass(StepDefinition::class)]
#[CoversClass(ParameterDefinition::class)]
#[CoversClass(InvalidStateMachineException::class)]
#[Small]
final class StateMachineDefinitionBuilderTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{non-empty-string, object}>
     */
    public static function invalidStateMachines(): array
    {
        return [
            'no rule' => [
                'it has no method with #[StateMachineRule]',
                new class
                {
                    #[StateMachineInvariant]
                    public function invariant(): void
                    {
                    }
                },
            ],
            'rule that is also an invariant' => [
                'method step() has more than one of #[StateMachineRule], #[InitializeStateMachine], and #[StateMachineInvariant]',
                new class
                {
                    #[StateMachineRule]
                    #[StateMachineInvariant]
                    public function step(): void
                    {
                    }
                },
            ],
            'rule that is not public' => [
                'rule step() is not public',
                new class
                {
                    #[StateMachineRule]
                    private function step(): void
                    {
                    }
                },
            ],
            'rule that is static' => [
                'rule step() is static',
                new class
                {
                    #[StateMachineRule]
                    public static function step(): void
                    {
                    }
                },
            ],
            'initialization that is not public' => [
                'initialization initialize() is not public',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[InitializeStateMachine]
                    protected function initialize(): void
                    {
                    }
                },
            ],
            'invariant that is static' => [
                'invariant invariant() is static',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[StateMachineInvariant]
                    public static function invariant(): void
                    {
                    }
                },
            ],
            'invariant with required parameters' => [
                'invariant invariant() has required parameters',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[StateMachineInvariant]
                    public function invariant(int $value): void
                    {
                    }
                },
            ],
            'precondition whose name is empty' => [
                'the precondition of rule step() is specified as a method whose name is empty',
                new class
                {
                    #[StateMachineRule(precondition: '')]
                    public function step(): void
                    {
                    }
                },
            ],
            'precondition that does not exist' => [
                'the precondition of rule step() is specified as doesNotExist(), which does not exist',
                new class
                {
                    #[StateMachineRule(precondition: 'doesNotExist')]
                    public function step(): void
                    {
                    }
                },
            ],
            'precondition that is not public' => [
                'the precondition canStep() of rule step() is not public',
                new class
                {
                    #[StateMachineRule(precondition: 'canStep')]
                    public function step(): void
                    {
                    }

                    private function canStep(): bool
                    {
                        return true;
                    }
                },
            ],
            'precondition with required parameters' => [
                'the precondition canStep() of rule step() has required parameters',
                new class
                {
                    #[StateMachineRule(precondition: 'canStep')]
                    public function step(): void
                    {
                    }

                    public function canStep(int $value): bool
                    {
                        return true;
                    }
                },
            ],
            'pool whose name is empty' => [
                'rule step() produces values for a pool whose name is empty',
                new class
                {
                    #[StateMachineRule(produces: '')]
                    public function step(): int
                    {
                        return 1;
                    }
                },
            ],
            'pool whose name is not a valid variable name' => [
                'rule step() produces values for pool "a-b", whose name is not a valid variable name',
                new class
                {
                    #[StateMachineRule(produces: 'a-b')]
                    public function step(): int
                    {
                        return 1;
                    }
                },
            ],
            'pool that is produced by a method whose return type is void' => [
                'initialization initialize() produces values for pool "values", but its return type is void',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[InitializeStateMachine(produces: 'values')]
                    public function initialize(): void
                    {
                    }
                },
            ],
            'variadic parameter' => [
                'variadic parameter $values of rule step() is not supported',
                new class
                {
                    #[StateMachineRule]
                    public function step(int ...$values): void
                    {
                    }
                },
            ],
            'parameter of a rule that is passed by reference' => [
                'parameter $value of rule step() is passed by reference, which is not supported',
                new class
                {
                    #[StateMachineRule]
                    public function step(int &$value): void
                    {
                    }
                },
            ],
            'parameter of an initialization that is passed by reference' => [
                'parameter $value of initialization initialize() is passed by reference, which is not supported',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[InitializeStateMachine]
                    public function initialize(int &$value): void
                    {
                    }
                },
            ],
            'pool that a parameter of an initialization takes values from' => [
                'parameter $value of initialization initialize() cannot take values from a pool, only the parameters of rules can',
                new class
                {
                    #[StateMachineRule(produces: 'values')]
                    public function step(): int
                    {
                        return 1;
                    }

                    #[InitializeStateMachine]
                    public function initialize(#[FromPool('values')] int $value): void
                    {
                    }
                },
            ],
            'pool and generator for the same parameter' => [
                'parameter $value of rule step() takes values from a pool, so its generator cannot be specified',
                new class
                {
                    #[StateMachineRule(produces: 'values')]
                    public function step(#[FromPool('values')] #[ForAll('values')] int $value): int
                    {
                        return 1;
                    }
                },
            ],
            'parameter that takes values from a pool whose name is empty' => [
                'parameter $value of rule step() takes values from a pool whose name is empty',
                new class
                {
                    #[StateMachineRule]
                    public function step(#[FromPool('')] int $value): void
                    {
                    }
                },
            ],
            'parameter that takes values from a pool that is not produced' => [
                'parameter $value of rule step() takes values from pool "values", for which no rule or initialization produces values',
                new class
                {
                    #[StateMachineRule]
                    public function step(#[FromPool('values')] int $value): void
                    {
                    }
                },
            ],
            'generator that is specified more than once' => [
                'the generator for parameter $value of rule step() is specified more than once',
                new class
                {
                    #[StateMachineRule]
                    public function step(#[ForAll('values')] #[ForAllExternal(ValidStateMachine::class, 'amounts')] int $value): void
                    {
                    }
                },
            ],
            'generator method that does not exist' => [
                'rule step(): the generator for parameter $value is specified as ' . ValidStateMachine::class . '::doesNotExist(), which does not exist',
                new class
                {
                    #[StateMachineRule]
                    public function step(#[ForAllExternal(ValidStateMachine::class, 'doesNotExist')] int $value): void
                    {
                    }
                },
            ],
            'generator method that does not return a value generator' => [
                '::values(), which is specified for parameter $value of rule step(), does not return a ' . ValueGenerator::class . ', but int',
                new class
                {
                    public static function values(): int
                    {
                        return 1;
                    }

                    #[StateMachineRule]
                    public function step(#[ForAll('values')] int $value): void
                    {
                    }
                },
            ],
            'parameter for which no generator can be derived' => [
                'rule step(): cannot derive a generator for parameter $value of type callable',
                new class
                {
                    #[StateMachineRule]
                    public function step(callable $value): void
                    {
                    }
                },
            ],
            'parameter whose class cannot be loaded' => [
                'rule step(): cannot derive a generator for parameter $value, Error was raised: Class "PHPUnit\TestFixture\PropertyBased\StateMachine\ParentClassThatDoesNotExist" not found',
                new class
                {
                    #[StateMachineRule]
                    public function step(ClassWithParentClassThatDoesNotExist $value): void
                    {
                    }
                },
            ],
        ];
    }

    /**
     * @return array<non-empty-string, array{class-string, non-empty-string, non-empty-string, object}>
     */
    public static function stateMachinesWithInvalidAttributes(): array
    {
        return [
            'rule with an argument that does not exist' => [
                StateMachineRule::class,
                'method %s::step()',
                'Unknown named parameter $preconditon',
                new class
                {
                    #[StateMachineRule]
                    public function anotherStep(): void
                    {
                    }

                    #[StateMachineRule(preconditon: 'canStep')]
                    public function step(): void
                    {
                    }

                    public function canStep(): bool
                    {
                        return true;
                    }
                },
            ],
            'initialization with an argument that does not exist' => [
                InitializeStateMachine::class,
                'method %s::initialize()',
                'Unknown named parameter $produce',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[InitializeStateMachine(produce: 'values')]
                    public function initialize(): int
                    {
                        return 1;
                    }
                },
            ],
            'invariant with an argument that does not exist' => [
                StateMachineInvariant::class,
                'method %s::invariant()',
                'Unknown named parameter $atTheEnd',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[StateMachineInvariant(atTheEnd: true)]
                    public function invariant(): void
                    {
                    }
                },
            ],
            'invariant with an argument of the wrong type' => [
                StateMachineInvariant::class,
                'method %s::invariant()',
                StateMachineInvariant::class . '::__construct(): Argument #1 ($atEnd) must be of type bool, string given, called in ' . __FILE__ . ' on line %d',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[StateMachineInvariant(atEnd: 'yes')]
                    public function invariant(): void
                    {
                    }
                },
            ],
            'rule that is specified more than once' => [
                StateMachineRule::class,
                'method %s::step()',
                'Attribute "' . StateMachineRule::class . '" must not be repeated',
                new class
                {
                    #[StateMachineRule]
                    #[StateMachineRule(produces: 'values')]
                    public function step(): int
                    {
                        return 1;
                    }
                },
            ],
            'pool with an argument that does not exist' => [
                FromPool::class,
                'parameter $value of method %s::step()',
                'Unknown named parameter $consumed',
                new class
                {
                    #[StateMachineRule(produces: 'values')]
                    public function produce(): int
                    {
                        return 1;
                    }

                    #[StateMachineRule]
                    public function step(#[FromPool('values', consumed: true)] int $value): void
                    {
                    }
                },
            ],
            'generator with an argument that does not exist' => [
                ForAll::class,
                'parameter $value of method %s::step()',
                'Unknown named parameter $generators',
                new class
                {
                    #[StateMachineRule]
                    public function step(#[ForAll(generators: 'values')] int $value): void
                    {
                    }
                },
            ],
            'generator of another class without the name of its method' => [
                ForAllExternal::class,
                'parameter $value of method %s::initialize()',
                'Too few arguments to function ' . ForAllExternal::class . '::__construct(), 1 passed in ' . __FILE__ . ' on line %d and exactly 2 expected',
                new class
                {
                    #[StateMachineRule]
                    public function step(): void
                    {
                    }

                    #[InitializeStateMachine]
                    public function initialize(#[ForAllExternal(ValidStateMachine::class)] int $value): void
                    {
                    }
                },
            ],
        ];
    }

    /**
     * Closures in attributes require PHP 8.5, so these state machines are
     * referred to by the names of their classes, which are only loaded by
     * the test that requires it.
     *
     * @return array<non-empty-string, array{non-empty-string, class-string}>
     */
    public static function invalidStateMachinesWithClosures(): array
    {
        return [
            'closure that does not return a value generator' => [
                'the closure that is specified with #[ForAll] for parameter $value of rule step() does not return a ' . ValueGenerator::class . ', but int',
                StateMachineWithClosureThatDoesNotReturnAValueGenerator::class,
            ],
            'closure that returns a value generator that is not supported' => [
                'rule step(): class ' . UnsupportedValueGenerator::class . ' implements ' . ValueGenerator::class . ', which is not supported',
                StateMachineWithClosureThatReturnsAnUnsupportedValueGenerator::class,
            ],
        ];
    }

    public function testBuildsTheDefinitionOfAStateMachine(): void
    {
        $definition = new StateMachineDefinitionBuilder()->build(ValidStateMachine::class, new GeneratorDeriver(GeneratorRegistry::empty()));

        $this->assertSame(ValidStateMachine::class, $definition->className());
        $this->assertSame(['balancesAreNotNegative'], $definition->invariants());
        $this->assertSame(['balancesAddUp'], $definition->invariantsAtEnd());

        [$openInitialAccount, $seed] = $definition->initializations();

        $this->assertSame('openInitialAccount', $openInitialAccount->methodName());
        $this->assertNull($openInitialAccount->precondition());
        $this->assertSame('accounts', $openInitialAccount->produces());
        $this->assertSame([], $openInitialAccount->parameters());

        $this->assertSame('seed', $seed->methodName());
        $this->assertNull($seed->produces());
        $this->assertCount(1, $seed->parameters());
        $this->assertSame('value', $seed->parameters()[0]->name());
        $this->assertNull($seed->parameters()[0]->pool());
        $this->assertNotNull($seed->parameters()[0]->generator());

        [$open, $deposit, $close] = $definition->rules();

        $this->assertSame('open', $open->methodName());
        $this->assertNull($open->precondition());
        $this->assertSame('accounts', $open->produces());
        $this->assertSame(AbstractGenerator::from(Generators::elements('a', 'b'))->label(), $open->parameters()[0]->generator()?->label());

        $this->assertSame('deposit', $deposit->methodName());
        $this->assertNull($deposit->produces());
        $this->assertSame('account', $deposit->parameters()[0]->name());
        $this->assertSame('accounts', $deposit->parameters()[0]->pool());
        $this->assertFalse($deposit->parameters()[0]->consume());
        $this->assertNull($deposit->parameters()[0]->generator());
        $this->assertSame('amount', $deposit->parameters()[1]->name());
        $this->assertNull($deposit->parameters()[1]->pool());
        $this->assertSame(AbstractGenerator::from(Generators::integers(1, 10))->label(), $deposit->parameters()[1]->generator()?->label());

        $this->assertSame('close', $close->methodName());
        $this->assertSame('hasAccounts', $close->precondition());
        $this->assertSame('accounts', $close->parameters()[0]->pool());
        $this->assertTrue($close->parameters()[0]->consume());
    }

    public function testMethodsOfAParentClassComeBeforeMethodsOfTheClassThatExtendsIt(): void
    {
        $definition = new StateMachineDefinitionBuilder()->build(ChildStateMachine::class, new GeneratorDeriver(GeneratorRegistry::empty()));

        $this->assertSame(
            ['initializeParent', 'initializeParentFurther', 'initializeChild'],
            array_map(static fn (StepDefinition $step): string => $step->methodName(), $definition->initializations()),
        );

        $this->assertSame(
            ['ruleOfParent', 'ruleOfChild', 'ruleThatIsOverridden'],
            array_map(static fn (StepDefinition $step): string => $step->methodName(), $definition->rules()),
        );

        $this->assertSame(['invariantOfParent', 'invariantOfChild'], $definition->invariants());
        $this->assertSame(['invariantAtEndOfParent', 'invariantAtEndOfChild'], $definition->invariantsAtEnd());
    }

    #[RequiresPhp('^8.5')]
    public function testTheGeneratorForAParameterOfAStepMayBeSpecifiedWithAClosure(): void
    {
        $definition = new StateMachineDefinitionBuilder()->build(StateMachineWithGeneratorSpecifiedWithClosure::class, new GeneratorDeriver(GeneratorRegistry::empty()));

        $this->assertSame(AbstractGenerator::from(Generators::elements('a', 'b'))->label(), $definition->rules()[0]->parameters()[0]->generator()?->label());
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('invalidStateMachines')]
    public function testRejectsAnInvalidStateMachine(string $message, object $machine): void
    {
        $this->assertIsRejected($message, $machine::class);
    }

    /**
     * @param class-string     $attributeName
     * @param non-empty-string $target
     * @param non-empty-string $error
     */
    #[DataProvider('stateMachinesWithInvalidAttributes')]
    public function testRejectsAStateMachineWithAnAttributeThatCannotBeInstantiated(string $attributeName, string $target, string $error, object $machine): void
    {
        try {
            new StateMachineDefinitionBuilder()->build($machine::class, new GeneratorDeriver(GeneratorRegistry::empty()));
        } catch (InvalidStateMachineException $e) {
            $this->assertStringMatchesFormat(
                sprintf(
                    "State machine %s cannot be run: invalid attribute %s for %s in %s:%%d\n%s",
                    $machine::class,
                    $attributeName,
                    sprintf($target, $machine::class),
                    __FILE__,
                    $error,
                ),
                $e->getMessage(),
            );

            return;
        }

        $this->fail('The state machine was not rejected');
    }

    /**
     * @param non-empty-string $message
     * @param class-string     $className
     */
    #[DataProvider('invalidStateMachinesWithClosures')]
    #[RequiresPhp('^8.5')]
    public function testRejectsAnInvalidStateMachineWithAClosure(string $message, string $className): void
    {
        $this->assertIsRejected($message, $className);
    }

    /**
     * @param non-empty-string $message
     * @param class-string     $className
     */
    private function assertIsRejected(string $message, string $className): void
    {
        try {
            new StateMachineDefinitionBuilder()->build($className, new GeneratorDeriver(GeneratorRegistry::empty()));
        } catch (InvalidStateMachineException $e) {
            $this->assertStringStartsWith('State machine ' . $className . ' cannot be run: ', $e->getMessage());
            $this->assertStringContainsString($message, $e->getMessage());

            return;
        }

        $this->fail('The state machine was not rejected');
    }
}
