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

use PHPUnit\Framework\TestCase;

class DataProviderDependencyTest extends TestCase
{
    public function testReference(): void
    {
        $this->markTestSkipped('This test should be skipped.');
        $this->assertTrue(true);
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/1896
     *
     * @depends testReference
     *
     * @dataProvider provider
     */
    public function testDependency($param): void
    {
    }

    public function provider()
    {
        $this->markTestSkipped('Any test with this data provider should be skipped.');

        return [];
    }
}
