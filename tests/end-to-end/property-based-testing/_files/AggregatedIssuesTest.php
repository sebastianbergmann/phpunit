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

final class AggregatedIssuesTest extends TestCase
{
    #[Property(examples: 10)]
    public function testPerformsNoAssertions(int $value): void
    {
    }

    /**
     * @param int<0, 1> $value
     */
    #[Property(examples: 10)]
    public function testPerformsAssertionsForSomeExamplesOnly(int $value): void
    {
        if ($value === 1) {
            $this->assertTrue(true);
        }
    }

    #[Property(examples: 10)]
    public function testTriggersADeprecationForEveryExample(int $value): void
    {
        trigger_error('deprecation', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }
}
