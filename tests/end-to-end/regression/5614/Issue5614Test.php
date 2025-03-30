<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5614;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Issue5614Test extends TestCase
{
    public static function provideRecursiveArray(): iterable
    {
        $array    = [];
        $array[0] = &$array;

        yield [$array];
    }

    #[DataProvider('provideRecursiveArray')]
    public function testRecursiveArray(array $array): void
    {
        $this->assertTrue(true);
    }
}
