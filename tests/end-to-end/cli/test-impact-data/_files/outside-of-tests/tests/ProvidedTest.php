<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\OutsideOfTests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProvidedTest extends TestCase
{
    /**
     * @return list<array{0: string}>
     */
    public static function cases(): array
    {
        return Cases::all();
    }

    #[DataProvider('cases')]
    public function testUsesWhatTheDataProviderProvides(string $case): void
    {
        $this->assertContains($case, ['a', 'b']);
    }
}
