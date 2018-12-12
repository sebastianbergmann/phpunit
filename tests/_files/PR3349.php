<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Test\PR3349;

use PHPUnit\Framework\TestCase;

final class Tests extends TestCase
{
    /**
     * @see \Test\PR3349\Tests::testSameClassDependencyCorrectOrder
     * @group PR3349_Group1
     */
    public function testSameClassDependencyCorrectOrderDependency(): int
    {
        $this->addToAssertionCount(1);

        return 100;
    }

    /**
     * @depends \Test\PR3349\Tests::testSameClassDependencyCorrectOrderDependency
     * @group PR3349_Group1
     */
    public function testSameClassDependencyCorrectOrder(int $int): void
    {
        $this->assertEquals(100, $int);
    }

    /**
     * @depends \Test\PR3349\Tests::testSameClassDependencyReversedOrderDependency
     * @group PR3349_Group2
     */
    public function testSameClassDependencyReversedOrder(int $int): void
    {
        $this->addToAssertionCount(1);
        $this->assertEquals(100, $int);
    }

    /**
     * @see \Test\PR3349\Tests::testSameClassDependencyReversedOrder
     * @group PR3349_Group2
     */
    public function testSameClassDependencyReversedOrderDependency(): int
    {
        $this->addToAssertionCount(1);

        return 100;
    }

    /**
     * @depends \Test\PR3349\Test_Dependencies::testExternalClassDependencySuccessReturn100
     * @group PR3349_Group3
     */
    public function testExternalClassDependencyCorrectOrder(int $int): void
    {
        $this->assertEquals(100, $int);
    }

    /**
     * @depends \Test\PR3349\Test_FailingSetUp::testExternalClassDependencySuccessReturn100WithFailingSetUp
     * @group PR3349_Group4
     */
    public function testExternalClassDependencyFailingSetUp(int $int): void
    {
        $this->assertEquals(100, $int);
    }

    /**
     * @depends \Test\PR3349\Test_ChainOne::testExternalClassDependencyChainFunctionOne
     * @group PR3349_Group5
     */
    public function testExternalClassChainedMultipleDependencies(int $int): void
    {
        $this->assertEquals(100, $int);
    }

    /**
     * @depends \Test\PR3349\Test_Dependencies::testExternalClassDependencyFailure
     * @group PR3349_Group6
     */
    public function testExternalClassDependencyFailed(int $int): void
    {
        $this->addToAssertionCount(1);
    }

    /**
     * @depends \Test\PR3349\Test_Dependencies::testExternalClassDependencySkipped
     * @group PR3349_Group7
     */
    public function testExternalClassDependencySkipped(int $int): void
    {
        $this->addToAssertionCount(1);
    }

    /**
     * @depends \Test\PR3349\Test_ChainOne::testExternalClassDependencyChainFunctionOneWithFailureInChain
     * @group PR3349_Group8
     */
    public function testExternalClassChainedMultipleDependenciesFailureInChain(int $int): void
    {
        $this->assertEquals(100, $int);
    }
}

final class Test_Dependencies extends TestCase
{
    /**
     * @see \Test\PR3349\Tests::testExternalClassDependencyCorrectOrder
     * @group PR3349_Group3
     */
    public function testExternalClassDependencySuccessReturn100(): int
    {
        $this->addToAssertionCount(1);

        return 100;
    }

    /**
     * @see \Test\PR3349\Tests::testExternalClassDependencyFailed
     * @group PR3349_Group6
     */
    public function testExternalClassDependencyFailure(): int
    {
        $this->fail('Something Went Wrong With Purpose.');

        return 100;
    }

    /**
     * @see \Test\PR3349\Tests::testExternalClassDependencySkipped
     * @group PR3349_Group7
     */
    public function testExternalClassDependencySkipped(): int
    {
        $this->fail('Test Skipped With Purpose.');

        return 100;
    }
}

final class Test_FailingSetUp extends TestCase
{
    /**
     * @throws \Exception
     * @group PR3349_Group4
     *
     * @see \Test\PR3349\Tests::testExternalClassDependencyFailingSetUp
     */
    public function setUp(): void
    {
        parent::setUp();

        throw new \Exception('This is failing with purpose.');
    }

    /**
     * @see \Test\PR3349\Tests::testExternalClassDependencyFailingSetUp
     * @group PR3349_Group4
     */
    public function testExternalClassDependencySuccessReturn100WithFailingSetUp(): int
    {
        $this->addToAssertionCount(1);

        return 100;
    }
}

final class Test_ChainOne extends TestCase
{
    /**
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependencies
     * @group PR3349_Group5
     * @depends \Test\PR3349\Test_ChainTwo::testExternalClassDependencyChainFunctionTwo
     */
    public function testExternalClassDependencyChainFunctionOne(int $int): int
    {
        $int += 40;
        $this->assertEquals(100, $int);

        return $int;
    }

    /**
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependenciesFailureInChain
     * @group PR3349_Group8
     * @depends \Test\PR3349\Test_ChainTwo::testExternalClassDependencyChainFunctionTwoFailure
     */
    public function testExternalClassDependencyChainFunctionOneWithFailureInChain(int $int): int
    {
        $int += 40;
        $this->assertEquals(100, $int);

        return $int;
    }
}

final class Test_ChainTwo extends TestCase
{
    /**
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependencies
     * @group PR3349_Group5
     * @depends \Test\PR3349\Test_ChainThree::testExternalClassDependencyChainFunctionThree
     */
    public function testExternalClassDependencyChainFunctionTwo(int $int): int
    {
        $int += 30;
        $this->assertEquals(60, $int);

        return $int;
    }

    /**
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependenciesFailureInChain
     * @group PR3349_Group8
     * @depends \Test\PR3349\Test_ChainThree::testExternalClassDependencyChainFunctionThree
     */
    public function testExternalClassDependencyChainFunctionTwoFailure(int $int): int
    {
        $int += 30;
        $this->assertEquals(60, $int);
        $this->fail('This failure is by purpose.');

        return $int;
    }
}

final class Test_ChainThree extends TestCase
{
    /**
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependencies
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependenciesFailureInChain
     * @group PR3349_Group5
     * @group PR3349_Group8
     * @depends \Test\PR3349\Test_ChainFour::testExternalClassDependencyChainFunctionFour
     */
    public function testExternalClassDependencyChainFunctionThree(int $int): int
    {
        $int += 20;
        $this->assertEquals(30, $int);

        return $int;
    }
}

final class Test_ChainFour extends TestCase
{
    /**
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependencies
     * @see \Test\PR3349\Tests::testExternalClassChainedMultipleDependenciesFailureInChain
     * @group PR3349_Group5
     * @group PR3349_Group8
     */
    public function testExternalClassDependencyChainFunctionFour(): int
    {
        $this->addToAssertionCount(1);

        return 10;
    }
}
