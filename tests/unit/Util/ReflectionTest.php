<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\BankAccountTest;
use PHPUnit\TestFixture\ConcreteTestClassExtendingAbstractTestClassWithoutTestSuffixTest;
use ReflectionClass;

#[CoversClass(Reflection::class)]
#[Small]
final class ReflectionTest extends TestCase
{
    public function testFindsSourceLocationForMethod(): void
    {
        $this->assertSame(
            [
                'file' => realpath(__DIR__ . '/../../_files/BankAccountTest.php'),
                'line' => 22,
            ],
            Reflection::sourceLocationFor(BankAccountTest::class, 'testBalanceIsInitiallyZero'),
        );
    }

    public function testFindsSourceLocationForMethodInAbstractTestCase(): void
    {
        $this->assertSame(
            [
                'file' => realpath(__DIR__ . '/../../_files/abstract/without-test-suffix/AbstractTestCase.php'),
                'line' => 16,
            ],
            Reflection::sourceLocationFor(ConcreteTestClassExtendingAbstractTestClassWithoutTestSuffixTest::class, 'testOne'),
        );
    }

    public function testReturnsUnknownSourceLocationForMethodThatDoesNotExist(): void
    {
        $this->assertSame(
            [
                'file' => 'unknown',
                'line' => 0,
            ],
            Reflection::sourceLocationFor('DoesNotExist', 'doesNotExist'),
        );
    }

    public function testFindsPublicMethodsInTestClass(): void
    {
        $methods = Reflection::publicMethodsDeclaredDirectlyInTestClass(new ReflectionClass(BankAccountTest::class));

        $this->assertCount(3, $methods);
        $this->assertSame('testBalanceIsInitiallyZero', $methods[0]->getName());
        $this->assertSame('testBalanceCannotBecomeNegative', $methods[1]->getName());
        $this->assertSame('testBalanceCannotBecomeNegative2', $methods[2]->getName());
    }

    public function testFindsMethodsInTestClass(): void
    {
        $methods = Reflection::methodsDeclaredDirectlyInTestClass(new ReflectionClass(BankAccountTest::class));

        $this->assertCount(3, $methods);
        $this->assertSame('testBalanceIsInitiallyZero', $methods[0]->getName());
        $this->assertSame('testBalanceCannotBecomeNegative', $methods[1]->getName());
        $this->assertSame('testBalanceCannotBecomeNegative2', $methods[2]->getName());
    }
}
