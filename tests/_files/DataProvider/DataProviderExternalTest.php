<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DataProvider;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class DataProviderExternalTest extends TestCase
{
    #[DataProviderExternal(DataProviderTest::class, 'publicProviderMethod')]
    #[DataProviderExternal(DataProviderTest::class, 'publicStaticProviderMethod')]
    #[DataProviderExternal(DataProviderTest::class, 'protectedProviderMethod')]
    #[DataProviderExternal(DataProviderTest::class, 'protectedStaticProviderMethod')]
    #[DataProviderExternal(DataProviderTest::class, 'privateProviderMethod')]
    #[DataProviderExternal(DataProviderTest::class, 'privateStaticProviderMethod')]
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }
}
