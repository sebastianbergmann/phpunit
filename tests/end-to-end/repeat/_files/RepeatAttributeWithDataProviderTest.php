<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Repeat;

use function sprintf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Repeat;
use PHPUnit\Framework\TestCase;

final class RepeatAttributeWithDataProviderTest extends TestCase
{
    /**
     * @var array<int, int>
     */
    private static array $count = [1 => 0, 2 => 0];

    /**
     * @return array<string, array{int}>
     */
    public static function provider(): array
    {
        return [
            'one' => [1],
            'two' => [2],
        ];
    }

    #[DataProvider('provider')]
    #[Repeat(3, 2)]
    public function testWithDataProvider(int $value): void
    {
        self::$count[$value]++;

        if ($value === 1 && self::$count[$value] <= 2) {
            $this->fail(sprintf('Failure on repetition %d of data set one', self::$count[$value]));
        }

        $this->assertGreaterThan(0, $value);
    }
}
