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

use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
final class ExamplesWithinTimeLimitTest extends TestCase
{
    /**
     * Each example stays within the time limit of one second for small
     * tests, all examples together exceed it.
     */
    #[Property(examples: 4)]
    public function testEachExampleStaysWithinTheTimeLimit(int $value): void
    {
        usleep(400000);

        $this->assertIsInt($value);
    }
}
