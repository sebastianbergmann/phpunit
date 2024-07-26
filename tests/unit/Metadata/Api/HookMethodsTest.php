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

use function array_keys;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\HookMethod;
use PHPUnit\Runner\HookMethodCollection;
use PHPUnit\TestFixture\TestWithHookMethodsPrioritizedTest;
use PHPUnit\TestFixture\TestWithHookMethodsTest;
use PHPUnit\TestFixture\TestWithoutHookMethodsTest;

#[CoversClass(HookMethods::class)]
#[Small]
#[Group('metadata')]
final class HookMethodsTest extends TestCase
{
    public function testReturnsDefaultHookMethodsForClassThatDoesNotExist(): void
    {
        $this->assertEquals(
            [
                'beforeClass'   => HookMethodCollection::defaultBeforeClass(),
                'before'        => HookMethodCollection::defaultBefore(),
                'preCondition'  => HookMethodCollection::defaultPreCondition(),
                'postCondition' => HookMethodCollection::defaultPostCondition(),
                'after'         => HookMethodCollection::defaultAfter(),
                'afterClass'    => HookMethodCollection::defaultAfterClass(),
            ],
            (new HookMethods)->hookMethods('does not exist'),
        );
    }

    public function testReturnsDefaultHookMethodsInTestClassWithoutHookMethods(): void
    {
        $this->assertEquals(
            [
                'beforeClass'   => HookMethodCollection::defaultBeforeClass(),
                'before'        => HookMethodCollection::defaultBefore(),
                'preCondition'  => HookMethodCollection::defaultPreCondition(),
                'postCondition' => HookMethodCollection::defaultPostCondition(),
                'after'         => HookMethodCollection::defaultAfter(),
                'afterClass'    => HookMethodCollection::defaultAfterClass(),
            ],
            (new HookMethods)->hookMethods(TestWithoutHookMethodsTest::class),
        );
    }

    public function testFindsHookMethodsInTestClassWithHookMethods(): void
    {
        $hookMethods = (new HookMethods)->hookMethods(TestWithHookMethodsTest::class);
        $this->assertSame(['beforeClass', 'before', 'preCondition', 'postCondition', 'after', 'afterClass'], array_keys($hookMethods));

        $beforeClassHooks = HookMethodCollection::defaultBeforeClass();
        $beforeClassHooks->add(new HookMethod('beforeFirstTestWithAttribute', 0));
        $beforeClassHooks->add(new HookMethod('beforeFirstTestWithAnnotation', 0));
        $this->assertEquals($beforeClassHooks, $hookMethods['beforeClass']);

        $beforeHooks = HookMethodCollection::defaultBefore();
        $beforeHooks->add(new HookMethod('beforeEachTestWithAttribute', 0));
        $beforeHooks->add(new HookMethod('beforeEachTestWithAnnotation', 0));
        $this->assertEquals($beforeHooks, $hookMethods['before']);

        $preConditionHooks = HookMethodCollection::defaultPreCondition();
        $preConditionHooks->add(new HookMethod('preConditionsWithAttribute', 0));
        $preConditionHooks->add(new HookMethod('preConditionsWithAnnotation', 0));
        $this->assertEquals($preConditionHooks, $hookMethods['preCondition']);

        $postConditionHooks = HookMethodCollection::defaultPostCondition();
        $postConditionHooks->add(new HookMethod('postConditionsWithAttribute', 0));
        $postConditionHooks->add(new HookMethod('postConditionsWithAnnotation', 0));
        $this->assertEquals($postConditionHooks, $hookMethods['postCondition']);

        $afterHooks = HookMethodCollection::defaultAfter();
        $afterHooks->add(new HookMethod('afterEachTestWithAttribute', 0));
        $afterHooks->add(new HookMethod('afterEachTestWithAnnotation', 0));
        $this->assertEquals($afterHooks, $hookMethods['after']);

        $afterClassHooks = HookMethodCollection::defaultAfterClass();
        $afterClassHooks->add(new HookMethod('afterLastTestWithAttribute', 0));
        $afterClassHooks->add(new HookMethod('afterLastTestWithAnnotation', 0));
        $this->assertEquals($afterClassHooks, $hookMethods['afterClass']);
    }

    public function testFindsHookMethodsInTestClassWithHookMethodsPrioritized(): void
    {
        $hookMethods = (new HookMethods)->hookMethods(TestWithHookMethodsPrioritizedTest::class);
        $this->assertSame(['beforeClass', 'before', 'preCondition', 'postCondition', 'after', 'afterClass'], array_keys($hookMethods));

        $beforeClassHooks = HookMethodCollection::defaultBeforeClass();
        $beforeClassHooks->add(new HookMethod('beforeFirstTest', 1));
        $this->assertEquals($beforeClassHooks, $hookMethods['beforeClass']);

        $beforeHooks = HookMethodCollection::defaultBefore();
        $beforeHooks->add(new HookMethod('beforeEachTest', 2));
        $this->assertEquals($beforeHooks, $hookMethods['before']);

        $preConditionHooks = HookMethodCollection::defaultPreCondition();
        $preConditionHooks->add(new HookMethod('preConditions', 3));
        $this->assertEquals($preConditionHooks, $hookMethods['preCondition']);

        $postConditionHooks = HookMethodCollection::defaultPostCondition();
        $postConditionHooks->add(new HookMethod('postConditions', 4));
        $this->assertEquals($postConditionHooks, $hookMethods['postCondition']);

        $afterHooks = HookMethodCollection::defaultAfter();
        $afterHooks->add(new HookMethod('afterEachTest', 5));
        $this->assertEquals($afterHooks, $hookMethods['after']);

        $afterClassHooks = HookMethodCollection::defaultAfterClass();
        $afterClassHooks->add(new HookMethod('afterLastTest', 6));
        $this->assertEquals($afterClassHooks, $hookMethods['afterClass']);
    }
}
