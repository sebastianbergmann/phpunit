<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\SkipInBeforeClass;

use function range;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotSkippedTest extends TestCase
{
    public static function provideData(): iterable
    {
        foreach (range(1, 100) as $item) {
            yield [true];
        }
    }

    #[DataProvider('provideData')]
    public function test1(bool $bool): void
    {
        $this->assertTrue($bool);
    }
}
