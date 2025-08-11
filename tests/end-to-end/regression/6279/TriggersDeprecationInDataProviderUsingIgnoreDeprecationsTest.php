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
namespace PHPUnit\TestFixture\Issue6279;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TriggersDeprecationInDataProviderUsingIgnoreDeprecationsTest extends TestCase
{
    public static function dataProvider1(): iterable
    {
        @trigger_error('some deprecation', E_USER_DEPRECATED);

        yield [true];
    }

    public static function dataProvider2(): iterable
    {
        @trigger_error('some deprecation 2', E_USER_DEPRECATED);

        yield [true];
    }

    public static function dataProvider3(): iterable
    {
        @trigger_error('some deprecation 3', E_USER_DEPRECATED);

        yield [true];
    }

    #[Test]
    #[DataProvider('dataProvider1')]
    #[IgnoreDeprecations]
    public function someMethod1(bool $value): void
    {
        $this->assertTrue($value);
    }

    #[Test]
    #[DataProvider('dataProvider2')]
    #[IgnoreDeprecations]
    public function method2_1(bool $value): void
    {
        $this->assertTrue($value);
    }

    #[Test]
    #[DataProvider('dataProvider2')]
    public function method2_2(bool $value): void
    {
        $this->assertTrue($value);
    }

    #[Test]
    #[DataProvider('dataProvider3')]
    public function method3_1(bool $value): void
    {
        $this->assertTrue($value);
    }

    #[Test]
    #[DataProvider('dataProvider3')]
    #[IgnoreDeprecations]
    public function method3_2(bool $value): void
    {
        $this->assertTrue($value);
    }
}
