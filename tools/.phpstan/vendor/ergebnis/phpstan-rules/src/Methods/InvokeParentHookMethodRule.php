<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Methods;

use Codeception\PHPUnit;
use Codeception\Test;
use Ergebnis\PHPStan\Rules\ClassName;
use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use Ergebnis\PHPStan\Rules\HasContent;
use Ergebnis\PHPStan\Rules\HookMethod;
use Ergebnis\PHPStan\Rules\Invocation;
use Ergebnis\PHPStan\Rules\MethodName;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;
use PHPStan\ShouldNotHappenException;
use PHPUnit\Framework;

/**
 * @implements Rules\Rule<Node\Stmt\ClassMethod>
 */
final class InvokeParentHookMethodRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Reflection\ReflectionProvider $reflectionProvider;

    /**
     * @var list<HookMethod>
     */
    private array $hookMethods;

    /**
     * @param array<class-string, array<string, string>> $hookMethods
     */
    public function __construct(
        Reflection\ReflectionProvider $reflectionProvider,
        array $hookMethods = []
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->hookMethods = self::sort(
            $reflectionProvider,
            ...self::filter(
                $reflectionProvider,
                ...\array_merge(
                    self::defaultHookMethods(),
                    \array_map(static function (array $hookMethod): HookMethod {
                        return HookMethod::create(
                            ClassName::fromString($hookMethod['className']),
                            MethodName::fromString($hookMethod['methodName']),
                            Invocation::fromString($hookMethod['invocation']),
                            HasContent::fromString($hookMethod['hasContent']),
                        );
                    }, $hookMethods),
                ),
            ),
        );
    }

    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'invokeParentHookMethod',
            'This rule reports an error when a hook method that overrides a hook method in a parent class does not invoke the overridden hook method in the expected order.',
            'A hook method such as `setUp()` that overrides a hook method of a parent class needs to invoke the parent hook method in the expected order, or the parent class does not set up or tear down as intended.',
            'Move the invocation of the parent hook method to the expected position.',
            Documentation\Parameter::create(
                'hookMethods',
                <<<'MARKDOWN'
                By default, this rule requires the following hook methods to be invoked before doing something in the overriding method:

                - [`Codeception\PHPUnit\TestCase::_setUp()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L11-L13)
                - [`Codeception\PHPUnit\TestCase::_setUpBeforeClass()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L25-L27)
                - [`Codeception\Test\Unit::_before()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L63-L65)
                - [`Codeception\Test\Unit::_setUp()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L34-L58)
                - [`PHPUnit\Framework\TestCase::assertPreConditions()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2073-L2075)
                - [`PHPUnit\Framework\TestCase::setUp()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2063-L2065)
                - [`PHPUnit\Framework\TestCase::setUpBeforeClass()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2055-L2057)

                By default, this rule requires the following hook methods to be invoked after doing something in the overriding method:

                - [`Codeception\PHPUnit\TestCase::_tearDown()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L18-L20)
                - [`Codeception\PHPUnit\TestCase::_tearDownAfterClass()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L32-L34)
                - [`Codeception\Test\Unit::_after()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L75-L77)
                - [`Codeception\Test\Unit::_tearDown()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L67-L70)
                - [`PHPUnit\Framework\TestCase::assertPostConditions()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2083-L2085)
                - [`PHPUnit\Framework\TestCase::tearDown()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2091-L2093)
                - [`PHPUnit\Framework\TestCase::tearDownAfterClass()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2098-L2100)

                You can set the `hookMethods` parameter to a list of additional hook methods, where each hook method has the following keys:

                - `className`: name of the class that declares the hook method
                - `hasContent`: one of `"yes"`, `"no"`
                - `invocation`: one of `"any"` (needs to be invoked), `"first"` (needs to be invoked before all other statements in the overriding hook method), `"last"` (needs to be invoked after all other statements in the overriding hook method)
                - `methodName`: name of the hook method
                MARKDOWN,
                [
                    [
                        'className' => 'Example\\Test\\Functional\\AbstractCest',
                        'hasContent' => 'yes',
                        'invocation' => 'first',
                        'methodName' => '_before',
                    ],
                ],
            ),
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example\Test;

                use PHPUnit\Framework;

                final class GreetingTest extends Framework\TestCase
                {
                    protected function setUp(): void
                    {
                        \putenv('GREETING=Hello');

                        parent::setUp();
                    }
                }
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example\Test;

                use PHPUnit\Framework;

                final class GreetingTest extends Framework\TestCase
                {
                    protected function setUp(): void
                    {
                        parent::setUp();

                        \putenv('GREETING=Hello');
                    }
                }
                PHP),
        );
    }

    public function getNodeType(): string
    {
        return Node\Stmt\ClassMethod::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        $classReflection = $scope->getClassReflection();

        if (null === $classReflection) {
            return [];
        }

        $parentClassReflection = $classReflection->getParentClass();

        if (null === $parentClassReflection) {
            return [];
        }

        $methodName = $node->name->toString();

        $hookMethod = $this->findMatchingHookMethod(
            $scope,
            $parentClassReflection,
            $classReflection,
            $methodName,
        );

        if (!$hookMethod instanceof HookMethod) {
            return [];
        }

        $statements = $node->getStmts();

        if (!\is_array($statements)) {
            throw new ShouldNotHappenException();
        }

        $parentHookMethodInvocation = self::findParentHookMethodInvocation(
            \array_values($statements),
            $hookMethod,
        );

        if ($parentHookMethodInvocation->equals(Invocation::never())) {
            if ($hookMethod->hasContent()->equals(HasContent::no())) {
                return [];
            }

            $message = \sprintf(
                'Method %s::%s() does not invoke parent::%s().',
                $classReflection->getName(),
                $methodName,
                $hookMethod->methodName()->toString(),
            );

            return [
                Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::invokeParentHookMethod()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build(),
            ];
        }

        if ($parentHookMethodInvocation->equals($hookMethod->invocation())) {
            return [];
        }

        if ($hookMethod->invocation()->equals(Invocation::first())) {
            $message = \sprintf(
                'Method %s::%s() does not invoke parent::%s() before all other statements.',
                $classReflection->getName(),
                $methodName,
                $hookMethod->methodName()->toString(),
            );

            return [
                Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::invokeParentHookMethod()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build(),
            ];
        }

        if ($hookMethod->invocation()->equals(Invocation::last())) {
            $message = \sprintf(
                'Method %s::%s() does not invoke parent::%s() after all other statements.',
                $classReflection->getName(),
                $methodName,
                $hookMethod->methodName()->toString(),
            );

            return [
                Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::invokeParentHookMethod()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build(),
            ];
        }

        throw new ShouldNotHappenException();
    }

    private function findMatchingHookMethod(
        Analyser\Scope $scope,
        Reflection\ClassReflection $parentClassReflection,
        Reflection\ClassReflection $classReflection,
        string $methodName
    ): ?HookMethod {
        foreach ($this->hookMethods as $hookMethod) {
            if (!$classReflection->isSubclassOfClass($this->reflectionProvider->getClass($hookMethod->className()->toString()))) {
                continue;
            }

            if (\mb_strtolower($hookMethod->methodName()->toString()) !== \mb_strtolower($methodName)) {
                continue;
            }

            $parentMethodReflection = $parentClassReflection->getMethod(
                $methodName,
                $scope,
            );

            $declaringClassReflection = $parentMethodReflection->getDeclaringClass();

            if (\mb_strtolower($hookMethod->className()->toString()) !== \mb_strtolower($declaringClassReflection->getName())) {
                return HookMethod::create(
                    ClassName::fromString($declaringClassReflection->getName()),
                    MethodName::fromString($methodName),
                    $hookMethod->invocation(),
                    HasContent::maybe(),
                );
            }

            return $hookMethod;
        }

        return null;
    }

    /**
     * @param list<Node\Stmt> $statements
     */
    private static function findParentHookMethodInvocation(
        array $statements,
        HookMethod $hookMethod
    ): Invocation {
        $statementsWithOperations = \array_filter($statements, static function (Node $statement): bool {
            if ($statement instanceof Node\Stmt\Nop) {
                return false;
            }

            return true;
        });

        $statementCount = \count($statementsWithOperations);

        foreach ($statementsWithOperations as $index => $statement) {
            if ($statement instanceof Node\Stmt\TryCatch) {
                if (!$statement->finally instanceof Node\Stmt\Finally_) {
                    continue;
                }

                $finallyInvocation = self::findParentHookMethodInvocation(
                    \array_values($statement->finally->stmts),
                    $hookMethod,
                );

                if ($finallyInvocation->equals(Invocation::never())) {
                    continue;
                }

                if (
                    $finallyInvocation->equals(Invocation::last())
                    && $statementCount - 1 === $index
                ) {
                    return Invocation::last();
                }

                return Invocation::any();
            }

            if (!self::isParentHookMethodInvocation($statement, $hookMethod)) {
                continue;
            }

            if (1 === $statementCount) {
                return $hookMethod->invocation();
            }

            if (0 === $index) {
                return Invocation::first();
            }

            if ($statementCount - 1 === $index) {
                return Invocation::last();
            }

            return Invocation::any();
        }

        return Invocation::never();
    }

    private static function isParentHookMethodInvocation(
        Node\Stmt $statement,
        HookMethod $hookMethod
    ): bool {
        if (!$statement instanceof Node\Stmt\Expression) {
            return false;
        }

        if (!$statement->expr instanceof Node\Expr\StaticCall) {
            return false;
        }

        if (!$statement->expr->class instanceof Node\Name) {
            return false;
        }

        $className = (string) $statement->expr->class;

        if (\mb_strtolower($className) !== 'parent') {
            return false;
        }

        if (!$statement->expr->name instanceof Node\Identifier) {
            return false;
        }

        return \mb_strtolower($statement->expr->name->toString()) === \mb_strtolower($hookMethod->methodName()->toString());
    }

    /**
     * @return list<HookMethod>
     */
    private static function filter(
        Reflection\ReflectionProvider $reflectionProvider,
        HookMethod ...$hookMethods
    ): array {
        return \array_values(\array_filter($hookMethods, static function (HookMethod $hookMethod) use ($reflectionProvider): bool {
            return $reflectionProvider->hasClass($hookMethod->className()->toString());
        }));
    }

    /**
     * @return list<HookMethod>
     */
    private static function sort(
        Reflection\ReflectionProvider $reflectionProvider,
        HookMethod ...$hookMethods
    ): array {
        \usort($hookMethods, static function (HookMethod $a, HookMethod $b) use ($reflectionProvider): int {
            if (\mb_strtolower($a->className()->toString()) === \mb_strtolower($b->className()->toString())) {
                return 0;
            }

            if ($reflectionProvider->getClass($a->className()->toString())->isSubclassOfClass($reflectionProvider->getClass($b->className()->toString()))) {
                return -1;
            }

            return 1;
        });

        return $hookMethods;
    }

    /**
     * @return list<HookMethod>
     */
    private static function defaultHookMethods(): array
    {
        return [
            /**
             * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2083-L2085
             */
            HookMethod::create(
                ClassName::fromString(Framework\TestCase::class),
                MethodName::fromString('assertPostConditions'),
                Invocation::last(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2073-L2075
             */
            HookMethod::create(
                ClassName::fromString(Framework\TestCase::class),
                MethodName::fromString('assertPreConditions'),
                Invocation::first(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2063-L2065
             */
            HookMethod::create(
                ClassName::fromString(Framework\TestCase::class),
                MethodName::fromString('setUp'),
                Invocation::first(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2055-L2057
             */
            HookMethod::create(
                ClassName::fromString(Framework\TestCase::class),
                MethodName::fromString('setUpBeforeClass'),
                Invocation::first(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2091-L2093
             */
            HookMethod::create(
                ClassName::fromString(Framework\TestCase::class),
                MethodName::fromString('tearDown'),
                Invocation::last(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2098-L2100
             */
            HookMethod::create(
                ClassName::fromString(Framework\TestCase::class),
                MethodName::fromString('tearDownAfterClass'),
                Invocation::last(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L11-L13
             */
            HookMethod::create(
                ClassName::fromString(PHPUnit\TestCase::class),
                MethodName::fromString('_setUp'),
                Invocation::first(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L25-L27
             */
            HookMethod::create(
                ClassName::fromString(PHPUnit\TestCase::class),
                MethodName::fromString('_setUpBeforeClass'),
                Invocation::first(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L18-L20
             */
            HookMethod::create(
                ClassName::fromString(PHPUnit\TestCase::class),
                MethodName::fromString('_tearDown'),
                Invocation::last(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L32-L34
             */
            HookMethod::create(
                ClassName::fromString(PHPUnit\TestCase::class),
                MethodName::fromString('_tearDownAfterClass'),
                Invocation::last(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L75-L77
             */
            HookMethod::create(
                ClassName::fromString(Test\Unit::class),
                MethodName::fromString('_after'),
                Invocation::last(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L63-L65
             */
            HookMethod::create(
                ClassName::fromString(Test\Unit::class),
                MethodName::fromString('_before'),
                Invocation::first(),
                HasContent::no(),
            ),
            /**
             * @see https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L34-L58
             */
            HookMethod::create(
                ClassName::fromString(Test\Unit::class),
                MethodName::fromString('_setUp'),
                Invocation::first(),
                HasContent::yes(),
            ),
            /**
             * @see https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L67-L70
             */
            HookMethod::create(
                ClassName::fromString(Test\Unit::class),
                MethodName::fromString('_tearDown'),
                Invocation::last(),
                HasContent::yes(),
            ),
        ];
    }
}
