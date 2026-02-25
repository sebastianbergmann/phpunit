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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DuplicateKeyAcrossProvidersTest extends TestCase
{
    public static function providerA(): array
    {
        return ['shared' => [1]];
    }

    public static function providerB(): array
    {
        return ['shared' => [2]];
    }

    #[DataProvider('providerA')]
    #[DataProvider('providerB')]
    public function testOne(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }
}
