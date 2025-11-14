<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6408;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class Issue6408Test extends TestCase
{
    public static function provideData(): iterable
    {
        yield from self::gatherAssertTypes();
    }

    public static function gatherAssertTypes(): array
    {
        throw new RuntimeException('a users exception from data-provider context');
    }

    #[DataProvider('provideData')]
    public function testFoo(): void
    {
    }
}
