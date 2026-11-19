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

use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class SlowTest extends TestCase
{
    /**
     * Checking this property for all of its examples takes longer than the
     * time limit for the test run.
     */
    #[Property(300)]
    public function testIsBeingCheckedWhenTheTimeLimitIsExceeded(int $value): void
    {
        usleep(100000);

        $this->assertIsInt($value);
    }
}
