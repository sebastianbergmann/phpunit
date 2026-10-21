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

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class IssueTriggeredByMinimalCounterexampleTest extends TestCase
{
    #[Property]
    public function testIsLessThanOneThousand(int $value): void
    {
        if ($value === 1000) {
            trigger_error('deprecation triggered by the minimal counterexample', E_USER_DEPRECATED);
        }

        $this->assertLessThan(1000, $value);
    }
}
