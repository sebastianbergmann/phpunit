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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DependsOnDataProviderSuccessTest extends TestCase
{
    /**
     * @return array<string, array{bool}>
     */
    public static function provider(): array
    {
        return [
            'one' => [true],
            'two' => [true],
        ];
    }

    #[DataProvider('provider')]
    public function testWithDataProvider(bool $value): void
    {
        $this->assertTrue($value);
    }

    #[Depends('testWithDataProvider')]
    public function testDependent(): void
    {
        $this->assertTrue(true);
    }
}
