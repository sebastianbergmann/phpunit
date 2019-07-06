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
        $this->assertTrue(true);
    }

    /**
     * @large
     */
    public function testWithSizeLarge(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @depends testDataProviderWithSizeMedium
     * @medium
     */
    public function testWithSizeMedium(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @small
     */
    public function testWithSizeSmall(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider provider
     * @small
     */
    public function testDataProviderWithSizeSmall(bool $value): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider provider
     * @medium
     */
    public function testDataProviderWithSizeMedium(bool $value): void
    {
        $this->assertTrue(true);
    }

    public function provider(): array
    {
        return [
            [false],
            [true],
        ];
    }
}
