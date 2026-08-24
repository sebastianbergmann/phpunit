<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

#[CoversClass(Money::class)]
final class TestWithAMissingDataProviderClass extends TestCase
{
    /** @phpstan-ignore class.notFound */
    #[DataProviderExternal(ThereIsNoSuchProviderClass::class, 'provide')]
    public function testOne(int $value): void
    {
        $this->assertSame(1, $value);
    }
}
