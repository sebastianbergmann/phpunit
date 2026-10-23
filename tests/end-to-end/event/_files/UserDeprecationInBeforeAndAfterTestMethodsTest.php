<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class UserDeprecationInBeforeAndAfterTestMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        trigger_error('deprecation in setUp()', E_USER_DEPRECATED);
    }

    protected function tearDown(): void
    {
        trigger_error('deprecation in tearDown()', E_USER_DEPRECATED);
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
