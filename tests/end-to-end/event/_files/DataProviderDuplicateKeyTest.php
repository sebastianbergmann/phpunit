<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataProviderDuplicateKeyTest extends TestCase
{
    public static function provider(): Generator
    {
        yield 'key' => [true];

        yield 'key' => [true];
    }

    #[DataProvider('provider')]
    public function testSomething(bool $value): void
    {
        $this->assertTrue($value);
    }
}
