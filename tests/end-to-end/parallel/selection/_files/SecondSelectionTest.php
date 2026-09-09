<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelSelection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class SecondSelectionTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'four' => ['four'],
            'five' => ['five'],
        ];
    }

    #[Group('beta')]
    public function testSecondPlain(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provider')]
    #[Group('alpha')]
    public function testSecondWithDataProvider(string $value): void
    {
        $this->assertNotEmpty($value);
    }
}
