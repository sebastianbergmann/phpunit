<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class DoesNotPerformAssertionsTest extends TestCase
{
    #[Property(examples: 10)]
    #[DoesNotPerformAssertions]
    public function testPerformsNoAssertions(int $value): void
    {
    }

    #[Property(examples: 10)]
    #[DoesNotPerformAssertions]
    public function testPerformsAnAssertionForEveryExample(int $value): void
    {
        $this->assertIsInt($value);
    }
}
