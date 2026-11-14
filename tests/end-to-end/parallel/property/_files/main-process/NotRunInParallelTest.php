<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelProperty;

use function getenv;
use PHPUnit\Framework\Attributes\DoNotRunInParallel;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

#[DoNotRunInParallel]
final class NotRunInParallelTest extends TestCase
{
    #[Property(examples: 10)]
    public function testExamplesAreRunInTheMainProcess(int $value): void
    {
        $this->assertFalse(getenv('PHPUNIT_WORKER_ID'));
    }

    /**
     * @param int<0, 1000> $a
     * @param int<0, 1000> $b
     */
    #[Property]
    public function testSumIsAtMostTen(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }
}
