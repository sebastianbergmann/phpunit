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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TriggersDeprecationInDataProviderTest extends TestCase
{
    public static function dataProvider(): iterable
    {
        @trigger_error('some deprecation', E_USER_DEPRECATED);

        yield [true];
    }

    #[Test]
    public function method1(): void
    {
        $this->assertTrue(true);
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function method2(bool $value): void
    {
        $this->assertTrue($value);
    }

    #[Test]
    public function method3(): void
    {
        $this->assertTrue(true);
    }
}
