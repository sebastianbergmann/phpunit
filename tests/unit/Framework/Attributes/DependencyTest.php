<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace unit\Framework\Attributes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class DependencyTest extends TestCase
{
    public static function dataProvider(): array
    {
        return [
            'case 1' => [
                'example' => '',
            ],
        ];
    }

    public function testOne(): void
    {
        $this->assertEmpty([]);
    }

    #[Depends('testOne')]
    #[DataProvider('dataProvider')]
    public function testTwo(string $example): void
    {
        $this->assertEmpty($example);
    }
}
