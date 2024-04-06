<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;

final class DataProviderDependencyTest extends TestCase
{
    public static function provider(): array
    {
        self::markTestSkipped('Any test with this data provider should be skipped.');
    }

    public function testReference(): void
    {
        $this->markTestSkipped('This test should be skipped.');
    }

    #[Depends('testReference')]
    #[DataProvider('provider')]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/1896')]
    public function testDependency($param): void
    {
    }
}
