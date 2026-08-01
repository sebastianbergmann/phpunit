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
use PHPUnit\Metadata\MetadataCollection;
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
 *
 * @phpstan-type HookMethodsByType array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}
 */
final class HookMethods
{
    /**
     * @var array<class-string, HookMethodsByType>
     */
    private static array $hookMethods = [];

    /**
     * @param class-string<TestCase> $className
     *
     * @return HookMethodsByType
     */
    public function hookMethods(string $className): array
    {
        if (!class_exists($className)) {
            return self::emptyHookMethodsArray();
        }

        if (isset(self::$hookMethods[$className])) {
            return self::$hookMethods[$className];
        }

        $hookMethods = self::emptyHookMethodsArray();

        foreach (Reflection::methodsDeclaredDirectlyInTestClass(new ReflectionClass($className)) as $method) {
            $methodName         = $method->getName();
            $declaringClassName = $method->getDeclaringClass()->getName();
            $metadata           = Registry::parser()->forMethod($className, $methodName);

            if ($method->isStatic()) {
                $this->addHookMethod($hookMethods['beforeClass'], $metadata->isBeforeClass(), $declaringClassName, $methodName, 'BeforeClass');
                $this->addHookMethod($hookMethods['afterClass'], $metadata->isAfterClass(), $declaringClassName, $methodName, 'AfterClass');
            }

            $this->addHookMethod($hookMethods['before'], $metadata->isBefore(), $declaringClassName, $methodName, 'Before');
            $this->addHookMethod($hookMethods['preCondition'], $metadata->isPreCondition(), $declaringClassName, $methodName, 'PreCondition');
            $this->addHookMethod($hookMethods['postCondition'], $metadata->isPostCondition(), $declaringClassName, $methodName, 'PostCondition');
            $this->addHookMethod($hookMethods['after'], $metadata->isAfter(), $declaringClassName, $methodName, 'After');
        }

        self::$hookMethods[$className] = $hookMethods;

        return $hookMethods;
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
     * @param class-string     $declaringClassName
     * @param non-empty-string $methodName
     * @param non-empty-string $attributeName
     */
    private function addHookMethod(HookMethodCollection $hookMethods, MetadataCollection $metadata, string $declaringClassName, string $methodName, string $attributeName): void
    {
        if ($metadata->isEmpty()) {
            return;
        }

        $hookMethod = $metadata->asArray()[0];

        assert(
            $hookMethod instanceof After ||
            $hookMethod instanceof AfterClass ||
            $hookMethod instanceof Before ||
            $hookMethod instanceof BeforeClass ||
            $hookMethod instanceof PostCondition ||
            $hookMethod instanceof PreCondition,
        );

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

        $hookMethods->add(new HookMethod($methodName, $hookMethod->priority()));
    }

    /**
     * @return HookMethodsByType
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
