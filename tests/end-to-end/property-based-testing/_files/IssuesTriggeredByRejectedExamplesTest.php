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

final class IssuesTriggeredByRejectedExamplesTest extends TestCase
{
    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 5)]
    public function testTriggersADeprecationForTheValuesThatAreRejected(int $value): void
    {
        if ($value % 2 !== 0) {
            trigger_error('deprecation triggered for a value that is rejected by assume()', E_USER_DEPRECATED);
        }

        $this->assume($value % 2 === 0);

        $this->assertSame(0, $value % 2);
    }
}
