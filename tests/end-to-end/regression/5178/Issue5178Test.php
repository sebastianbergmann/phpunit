<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class Issue5178Test extends TestCase
{
    public static function fooDataProvider(): array
    {
        return [
            'foo 1' => ['Hello'],
            'foo 2' => ['World'],
        ];
    }

    #[DataProvider('fooDataProvider')]
    public function testFoo(string $input): void
    {
        $this->assertNotEmpty($input);
    }

    #[Depends('testFoo')]
    public function testBar(): void
    {
        $this->assertTrue(true);
    }
}
