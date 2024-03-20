<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function call_user_func_array;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Stub\ClosureMock;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/mock-object')]
#[Medium]
#[TestDox('createClosureMock()')]
final class CreateClosureMockTest extends TestCase
{
    public function testCreateClosureMock(): void
    {
        $mock = $this->createClosureMock();

        $this->assertInstanceOf(ClosureMock::class, $mock);
        $this->assertInstanceOf(Stub::class, $mock);
    }

    public function testCreateClosureMockWithReturnValue(): void
    {
        $mock = $this->createClosureMock();

        $mock->closure()->willReturn(123);

        $this->assertSame(123, $mock());
    }

    public function testCreateClosureMockWithExpectation(): void
    {
        $mock = $this->createClosureMock();

        $mock->expectsClosure($this->once())
            ->willReturn(123);

        $this->assertSame(123, $mock());
    }

    public function testClosureMockAppliesExpects(): void
    {
        $mock = $this->createClosureMock();

        $mock->expectsClosure($this->once());

        $this->assertThatMockObjectExpectationFails(
            "Expectation failed for method name is \"__invoke\" when invoked 1 time.\nMethod was expected to be called 1 time, actually called 0 times.\n",
            $mock,
        );
    }

    private function assertThatMockObjectExpectationFails(string $expectationFailureMessage, MockObject $mock, string $methodName = '__phpunit_verify', array $arguments = []): void
    {
        try {
            call_user_func_array([$mock, $methodName], $arguments);
        } catch (ExpectationFailedException|MatchBuilderNotFoundException $e) {
            $this->assertSame($expectationFailureMessage, $e->getMessage());

            return;
        } finally {
            $this->resetMockObjects();
        }

        $this->fail();
    }

    private function resetMockObjects(): void
    {
        (new ReflectionProperty(TestCase::class, 'mockObjects'))->setValue($this, []);
    }
}
