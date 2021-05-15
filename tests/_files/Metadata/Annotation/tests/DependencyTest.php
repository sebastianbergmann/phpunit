<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Annotation;

use PHPUnit\Framework\TestCase;

final class DependencyTest extends TestCase
{
    /**
     * @depends PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::testOne
     * @depends !clone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::testOne
     * @depends clone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::testOne
     * @depends !shallowClone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::testOne
     * @depends shallowClone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::testOne
     */
    public function testOne(): void
    {
    }

    /**
     * @depends testOne
     * @depends !clone testOne
     * @depends clone testOne
     * @depends !shallowClone testOne
     * @depends shallowClone testOne
     */
    public function testTwo(): void
    {
    }

    /**
     * @depends PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::class
     * @depends !clone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::class
     * @depends clone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::class
     * @depends !shallowClone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::class
     * @depends shallowClone PHPUnit\TestFixture\Metadata\Annotation\AnotherTest::class
     */
    public function testThree(): void
    {
    }
}
