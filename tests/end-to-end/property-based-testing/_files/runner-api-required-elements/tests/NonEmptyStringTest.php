<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApiRequiredElements;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class NonEmptyStringTest extends TestCase
{
    /**
     * @param non-empty-string $value
     */
    #[Property]
    public function testDrawsANonEmptyString(string $value): void
    {
        $this->assertNotSame('', $value);
    }
}
