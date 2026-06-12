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

final class DataProviderAllAttemptsFailTest extends TestCase
{
    /**
     * @var array<string, int>
     */
    private static array $counts = [];

    /**
     * @return array<string, array{string}>
     */
    public static function provider(): array
    {
        return [
            'stable'  => ['stable'],
            'flaky'   => ['flaky'],
            'failing' => ['failing'],
        ];
    }

    #[DataProvider('provider')]
    #[Retry(2)]
    public function testWithDataProvider(string $behaviour): void
    {
        if (!isset(self::$counts[$behaviour])) {
            self::$counts[$behaviour] = 0;
        }

        self::$counts[$behaviour]++;

        if ($behaviour === 'failing') {
            $this->fail('Failure for failing data set');
        }

        if ($behaviour === 'flaky' && self::$counts[$behaviour] < 2) {
            $this->fail('Failure on first attempt for flaky data set');
        }

        $this->assertTrue(true);
    }
}
