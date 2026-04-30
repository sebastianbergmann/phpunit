<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6599;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class Issue6599Test extends TestCase
{
    public static function provider(): array
    {
        return [
            'one' => [1],
            'two' => [2],
        ];
    }

    protected function setUp(): void
    {
        throw new RuntimeException('failure in setUp');
    }

    #[DataProvider('provider')]
    public function testWithDataProvider(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }

    public function testWithoutDataProvider(): void
    {
        $this->assertTrue(true);
    }
}
