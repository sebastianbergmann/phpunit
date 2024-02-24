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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestWithHookMethodsTest;
use PHPUnit\TestFixture\TestWithoutHookMethodsTest;

#[CoversClass(HookMethods::class)]
#[Small]
#[Group('metadata')]
final class HookMethodsTest extends TestCase
{
    public function testReturnsDefaultHookMethodsForClassThatDoesNotExist(): void
    {
        $this->assertSame(
            [
                'beforeClass' => [
                    'setUpBeforeClass',
                ],
                'before' => [
                    'setUp',
                ],
                'preCondition' => [
                    'assertPreConditions',
                ],
                'postCondition' => [
                    'assertPostConditions',
                ],
                'after' => [
                    'tearDown',
                ],
                'afterClass' => [
                    'tearDownAfterClass',
                ],
            ],
            (new HookMethods)->hookMethods('does not exist'),
        );
    }

    public function testReturnsDefaultHookMethodsInTestClassWithoutHookMethods(): void
    {
        $this->assertSame(
            [
                'beforeClass' => [
                    'setUpBeforeClass',
                ],
                'before' => [
                    'setUp',
                ],
                'preCondition' => [
                    'assertPreConditions',
                ],
                'postCondition' => [
                    'assertPostConditions',
                ],
                'after' => [
                    'tearDown',
                ],
                'afterClass' => [
                    'tearDownAfterClass',
                ],
            ],
            (new HookMethods)->hookMethods(TestWithoutHookMethodsTest::class),
        );
    }

    public function testFindsHookMethodsInTestClassWithHookMethods(): void
    {
        $this->assertSame(
            [
                'beforeClass' => [
                    'beforeFirstTestWithAnnotation',
                    'beforeFirstTestWithAttribute',
                    'setUpBeforeClass',
                ],
                'before' => [
                    'beforeEachTestWithAnnotation',
                    'beforeEachTestWithAttribute',
                    'setUp',
                ],
                'preCondition' => [
                    'preConditionsWithAnnotation',
                    'preConditionsWithAttribute',
                    'assertPreConditions',
                ],
                'postCondition' => [
                    'assertPostConditions',
                    'postConditionsWithAttribute',
                    'postConditionsWithAnnotation',
                ],
                'after' => [
                    'tearDown',
                    'afterEachTestWithAttribute',
                    'afterEachTestWithAnnotation',
                ],
                'afterClass' => [
                    'tearDownAfterClass',
                    'afterLastTestWithAttribute',
                    'afterLastTestWithAnnotation',
                ],
            ],
            (new HookMethods)->hookMethods(TestWithHookMethodsTest::class),
        );
    }
}
