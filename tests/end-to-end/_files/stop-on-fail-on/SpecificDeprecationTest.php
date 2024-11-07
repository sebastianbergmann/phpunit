<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestRunnerStopping;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class SpecificDeprecationTest extends TestCase
{
    public function testOne(): void
    {
        trigger_error('...foo...', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        trigger_error('...bar...', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        trigger_error('...baz...', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }
}
