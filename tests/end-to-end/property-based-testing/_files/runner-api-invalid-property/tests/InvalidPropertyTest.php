<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class InvalidPropertyTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testIsValid(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    public function testUsesAGeneratorThatDoesNotExist(#[ForAll('doesNotExist')] int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
