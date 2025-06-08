<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2025 Andreas Möller
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
final class InvokeParentHookMethodRule implements Rules\Rule
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
            if (!$statement instanceof Node\Stmt\Expression) {
                continue;
            }

            if (!$statement->expr instanceof Node\Expr\StaticCall) {
                continue;
            }

            if (!$statement->expr->class instanceof Node\Name) {
                continue;
            }

            $className = (string) $statement->expr->class;

            if (\mb_strtolower($className) !== 'parent') {
                continue;
            }

            if (!$statement->expr->name instanceof Node\Identifier) {
                continue;
            }

            if (\mb_strtolower($statement->expr->name->toString()) === \mb_strtolower($hookMethod->methodName()->toString())) {
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
        }

        return Invocation::never();
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
