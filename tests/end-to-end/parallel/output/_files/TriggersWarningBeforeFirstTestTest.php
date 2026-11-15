<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelOutput;

use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class TriggersWarningBeforeFirstTestTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // the error handler of PHPUnit does not handle an error that is
        // triggered here, so PHP displays it
        trigger_error('warning triggered before the first test', E_USER_WARNING);
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
