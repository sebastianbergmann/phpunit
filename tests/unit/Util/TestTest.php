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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestCaseTest;
use ReflectionMethod;

#[CoversClass(Test::class)]
#[Small]
final class TestTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [true, new ReflectionMethod(TestCaseTest::class, 'testOne')],
            [true, new ReflectionMethod(TestCaseTest::class, 'two')],
            [true, new ReflectionMethod(TestCaseTest::class, 'three')],
            [false, new ReflectionMethod(TestCaseTest::class, 'four')],
            [false, new ReflectionMethod(TestCaseTest::class, 'five')],
        ];
    }

    #[DataProvider('provider')]
    public function testDetectsTestMethods(bool $result, ReflectionMethod $method): void
    {
        $this->assertSame($result, Test::isTestMethod($method));
    }
}
