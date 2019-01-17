<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Issue3093Test extends \PHPUnit\Framework\TestCase
{
    public function someDataProvider(): array
    {
        return [['some values']];
    }

    public function testFirstWithoutDependencies(): void
    {
        self::assertTrue(true);
    }

    /**
     * @depends testFirstWithoutDependencies
     * @dataProvider someDataProvider
     */
    public function testSecondThatDependsOnFirstAndDataprovider($value): void
    {
        self::assertTrue(true);
    }
}
