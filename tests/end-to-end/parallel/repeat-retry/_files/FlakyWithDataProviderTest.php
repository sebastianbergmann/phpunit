<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelRepeatRetry;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class FlakyWithDataProviderTest extends TestCase
{
    /**
     * @var array<int, int>
     */
    private static array $attempts = [];

    public static function provider(): array
    {
        return [
            'one' => [1],
            'two' => [2],
        ];
    }

    #[DataProvider('provider')]
    #[Retry(3)]
    public function testFlaky(int $value): void
    {
        if (!isset(self::$attempts[$value])) {
            self::$attempts[$value] = 0;
        }

        self::$attempts[$value]++;

        if ($value === 2 && self::$attempts[$value] < 3) {
            $this->fail('Flaky failure on attempt ' . self::$attempts[$value]);
        }

        $this->assertSame($value, $value);
    }
}
