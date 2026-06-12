<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Retry;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class RetryWithDataProviderTest extends TestCase
{
    /**
     * @var array<int, int>
     */
    private static array $counts = [];

    /**
     * @return array<string, array{int}>
     */
    public static function provider(): array
    {
        return [
            'stable' => [1],
            'flaky'  => [2],
        ];
    }

    #[DataProvider('provider')]
    #[Retry(3)]
    public function testWithDataProvider(int $value): void
    {
        if (!isset(self::$counts[$value])) {
            self::$counts[$value] = 0;
        }

        self::$counts[$value]++;

        if ($value === 2 && self::$counts[$value] < 2) {
            $this->fail('Failure on first attempt for flaky data set');
        }

        $this->assertTrue(true);
    }
}
