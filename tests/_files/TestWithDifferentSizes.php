<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

final class TestWithDifferentSizes extends TestCase
{
    public function testWithSizeUnknown(): void
    {
    }

    /**
     * @large
     */
    public function testWithSizeLarge(): void
    {
    }

    /**
     * @depends testDataProviderWithSizeMedium
     * @medium
     */
    public function testWithSizeMedium(): void
    {
    }

    /**
     * @small
     */
    public function testWithSizeSmall(): void
    {
    }

    /**
     * @dataProvider provider
     * @small
     */
    public function testDataProviderWithSizeSmall(bool $value): void
    {
    }

    /**
     * @dataProvider provider
     * @medium
     */
    public function testDataProviderWithSizeMedium(bool $value): void
    {
    }

    public function provider(): array
    {
        return [
            [false],
            [true],
        ];
    }
}
