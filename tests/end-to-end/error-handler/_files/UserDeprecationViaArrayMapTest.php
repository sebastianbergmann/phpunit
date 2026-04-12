<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler;

use const E_USER_DEPRECATED;
use function array_map;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class UserDeprecationViaArrayMapTest extends TestCase
{
    public function testUserDeprecationTriggeredInsideArrayMapCallback(): void
    {
        array_map(static function (int $value): int
        {
            @trigger_error('deprecation from array_map callback', E_USER_DEPRECATED);

            return $value;
        }, [1]);

        $this->assertTrue(true);
    }
}
