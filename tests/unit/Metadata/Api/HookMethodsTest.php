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
                'beforeClass'   => HookMethodsCollection::defaultBeforeClass(),
                'before'        => HookMethodsCollection::defaultBefore(),
                'preCondition'  => HookMethodsCollection::defaultPreCondition(),
                'postCondition' => HookMethodsCollection::defaultPostCondition(),
                'after'         => HookMethodsCollection::defaultAfter(),
                'afterClass'    => HookMethodsCollection::defaultAfterClass(),
            ],
            (new HookMethods)->hookMethods('does not exist'),
        );
    }

    public function testReturnsDefaultHookMethodsInTestClassWithoutHookMethods(): void
    {
        $this->assertEquals(
            [
                'beforeClass'   => HookMethodsCollection::defaultBeforeClass(),
                'before'        => HookMethodsCollection::defaultBefore(),
                'preCondition'  => HookMethodsCollection::defaultPreCondition(),
                'postCondition' => HookMethodsCollection::defaultPostCondition(),
                'after'         => HookMethodsCollection::defaultAfter(),
                'afterClass'    => HookMethodsCollection::defaultAfterClass(),
            ],
            (new HookMethods)->hookMethods(TestWithoutHookMethodsTest::class),
        );
    }

    public function testFindsHookMethodsInTestClassWithHookMethods(): void
    {
        $hookMethods = (new HookMethods)->hookMethods(TestWithHookMethodsTest::class);
        $this->assertSame(['beforeClass', 'before', 'preCondition', 'postCondition', 'after', 'afterClass'], array_keys($hookMethods));

        $beforeClassHooks = HookMethodsCollection::defaultBeforeClass();
        $beforeClassHooks->add(new HookMethod('beforeFirstTestWithAttribute'));
        $beforeClassHooks->add(new HookMethod('beforeFirstTestWithAnnotation'));
        $this->assertEquals($beforeClassHooks, $hookMethods['beforeClass']);

        $beforeHooks = HookMethodsCollection::defaultBefore();
        $beforeHooks->add(new HookMethod('beforeEachTestWithAttribute'));
        $beforeHooks->add(new HookMethod('beforeEachTestWithAnnotation'));
        $this->assertEquals($beforeHooks, $hookMethods['before']);

        $preConditionHooks = HookMethodsCollection::defaultPreCondition();
        $preConditionHooks->add(new HookMethod('preConditionsWithAttribute'));
        $preConditionHooks->add(new HookMethod('preConditionsWithAnnotation'));
        $this->assertEquals($preConditionHooks, $hookMethods['preCondition']);

        $postConditionHooks = HookMethodsCollection::defaultPostCondition();
        $postConditionHooks->add(new HookMethod('postConditionsWithAttribute'));
        $postConditionHooks->add(new HookMethod('postConditionsWithAnnotation'));
        $this->assertEquals($postConditionHooks, $hookMethods['postCondition']);

        $afterHooks = HookMethodsCollection::defaultAfter();
        $afterHooks->add(new HookMethod('afterEachTestWithAttribute'));
        $afterHooks->add(new HookMethod('afterEachTestWithAnnotation'));
        $this->assertEquals($afterHooks, $hookMethods['after']);

        $afterClassHooks = HookMethodsCollection::defaultAfterClass();
        $afterClassHooks->add(new HookMethod('afterLastTestWithAttribute'));
        $afterClassHooks->add(new HookMethod('afterLastTestWithAnnotation'));
        $this->assertEquals($afterClassHooks, $hookMethods['afterClass']);
    }

    public function testFindsHookMethodsInTestClassWithHookMethodsPrioritized(): void
    {
        $hookMethods = (new HookMethods)->hookMethods(TestWithHookMethodsPrioritizedTest::class);
        $this->assertSame(['beforeClass', 'before', 'preCondition', 'postCondition', 'after', 'afterClass'], array_keys($hookMethods));

        $beforeClassHooks = HookMethodsCollection::defaultBeforeClass();
        $beforeClassHooks->add(new HookMethod('beforeFirstTest', priority: 1));
        $this->assertEquals($beforeClassHooks, $hookMethods['beforeClass']);

        $beforeHooks = HookMethodsCollection::defaultBefore();
        $beforeHooks->add(new HookMethod('beforeEachTest', priority: 2));
        $this->assertEquals($beforeHooks, $hookMethods['before']);

        $preConditionHooks = HookMethodsCollection::defaultPreCondition();
        $preConditionHooks->add(new HookMethod('preConditions', priority: 3));
        $this->assertEquals($preConditionHooks, $hookMethods['preCondition']);

        $postConditionHooks = HookMethodsCollection::defaultPostCondition();
        $postConditionHooks->add(new HookMethod('postConditions', priority: 4));
        $this->assertEquals($postConditionHooks, $hookMethods['postCondition']);

        $afterHooks = HookMethodsCollection::defaultAfter();
        $afterHooks->add(new HookMethod('afterEachTest', priority: 5));
        $this->assertEquals($afterHooks, $hookMethods['after']);

        $afterClassHooks = HookMethodsCollection::defaultAfterClass();
        $afterClassHooks->add(new HookMethod('afterLastTest', priority: 6));
        $this->assertEquals($afterClassHooks, $hookMethods['afterClass']);
    }
}
