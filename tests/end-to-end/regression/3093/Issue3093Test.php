<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class Issue3093Test extends TestCase
{
    public static function someDataProvider(): array
    {
        return [['some values']];
    }

    public function testFirstWithoutDependencies(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testFirstWithoutDependencies')]
    #[DataProvider('someDataProvider')]
    public function testSecondThatDependsOnFirstAndDataprovider($value): void
    {
        $this->assertTrue(true);
    }
}
