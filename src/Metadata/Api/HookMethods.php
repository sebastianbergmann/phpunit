<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use function assert;
use function class_exists;
use function in_array;
use function sprintf;
use function strtolower;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\After;
use PHPUnit\Metadata\AfterClass;
use PHPUnit\Metadata\Before;
use PHPUnit\Metadata\BeforeClass;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\PostCondition;
use PHPUnit\Metadata\PreCondition;
use PHPUnit\Runner\HookMethod;
use PHPUnit\Runner\HookMethodCollection;
use PHPUnit\Util\Reflection;
use ReflectionClass;
use ReflectionMethod;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class HookMethods
{
    /**
     * @var array<class-string, array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}>
     */
    private static array $hookMethods = [];

    /**
     * @param class-string<TestCase> $className
     *
     * @return array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}
     */
    public function hookMethods(string $className): array
    {
        if (!class_exists($className)) {
            return self::emptyHookMethodsArray();
        }

        if (isset(self::$hookMethods[$className])) {
            return self::$hookMethods[$className];
        }

        self::$hookMethods[$className] = self::emptyHookMethodsArray();

        foreach (Reflection::methodsDeclaredDirectlyInTestClass(new ReflectionClass($className)) as $method) {
            $methodName         = $method->getName();
            $declaringClassName = $method->getDeclaringClass()->getName();
            $metadata           = Registry::parser()->forMethod($className, $methodName);

            if ($method->isStatic()) {
                if ($metadata->isBeforeClass()->isNotEmpty()) {
                    $beforeClass = $metadata->isBeforeClass()->asArray()[0];
                    assert($beforeClass instanceof BeforeClass);

                    $this->addHookMethod(
                        self::$hookMethods[$className]['beforeClass'],
                        $declaringClassName,
                        $methodName,
                        $beforeClass->priority(),
                        'BeforeClass',
                    );
                }

                if ($metadata->isAfterClass()->isNotEmpty()) {
                    $afterClass = $metadata->isAfterClass()->asArray()[0];
                    assert($afterClass instanceof AfterClass);

                    $this->addHookMethod(
                        self::$hookMethods[$className]['afterClass'],
                        $declaringClassName,
                        $methodName,
                        $afterClass->priority(),
                        'AfterClass',
                    );
                }
            }

            if ($metadata->isBefore()->isNotEmpty()) {
                $before = $metadata->isBefore()->asArray()[0];
                assert($before instanceof Before);

                $this->addHookMethod(
                    self::$hookMethods[$className]['before'],
                    $declaringClassName,
                    $methodName,
                    $before->priority(),
                    'Before',
                );
            }

            if ($metadata->isPreCondition()->isNotEmpty()) {
                $preCondition = $metadata->isPreCondition()->asArray()[0];
                assert($preCondition instanceof PreCondition);

                $this->addHookMethod(
                    self::$hookMethods[$className]['preCondition'],
                    $declaringClassName,
                    $methodName,
                    $preCondition->priority(),
                    'PreCondition',
                );
            }

            if ($metadata->isPostCondition()->isNotEmpty()) {
                $postCondition = $metadata->isPostCondition()->asArray()[0];
                assert($postCondition instanceof PostCondition);

                $this->addHookMethod(
                    self::$hookMethods[$className]['postCondition'],
                    $declaringClassName,
                    $methodName,
                    $postCondition->priority(),
                    'PostCondition',
                );
            }

            if ($metadata->isAfter()->isNotEmpty()) {
                $after = $metadata->isAfter()->asArray()[0];
                assert($after instanceof After);

                $this->addHookMethod(
                    self::$hookMethods[$className]['after'],
                    $declaringClassName,
                    $methodName,
                    $after->priority(),
                    'After',
                );
            }
        }

        return self::$hookMethods[$className];
    }

    public function isHookMethod(ReflectionMethod $method): bool
    {
        $defaultNames = [
            'setupbeforeclass',
            'setup',
            'assertpreconditions',
            'assertpostconditions',
            'teardown',
            'teardownafterclass',
        ];

        if (in_array(strtolower($method->getName()), $defaultNames, true)) {
            return true;
        }

        $metadata = Registry::parser()->forMethod($method->getDeclaringClass()->getName(), $method->getName());

        return $metadata->isBeforeClass()->isNotEmpty() ||
            $metadata->isBefore()->isNotEmpty() ||
            $metadata->isPreCondition()->isNotEmpty() ||
            $metadata->isPostCondition()->isNotEmpty() ||
            $metadata->isAfter()->isNotEmpty() ||
            $metadata->isAfterClass()->isNotEmpty();
    }

    /**
     * @param class-string<TestCase> $declaringClassName
     * @param non-empty-string       $methodName
     * @param non-empty-string       $attributeName
     */
    private function addHookMethod(HookMethodCollection $hookMethods, string $declaringClassName, string $methodName, int $priority, string $attributeName): void
    {
        if ($hookMethods->isDefaultHookMethod($methodName)) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Method %s::%s() is a template method and does not need the #[%s] attribute; the attribute is ignored',
                    $declaringClassName,
                    $methodName,
                    $attributeName,
                ),
            );

            return;
        }

        $hookMethods->add(new HookMethod($methodName, $priority));
    }

    /**
     * @return array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}
     */
    private function emptyHookMethodsArray(): array
    {
        return [
            'beforeClass'   => HookMethodCollection::defaultBeforeClass(),
            'before'        => HookMethodCollection::defaultBefore(),
            'preCondition'  => HookMethodCollection::defaultPreCondition(),
            'postCondition' => HookMethodCollection::defaultPostCondition(),
            'after'         => HookMethodCollection::defaultAfter(),
            'afterClass'    => HookMethodCollection::defaultAfterClass(),
        ];
    }
}
