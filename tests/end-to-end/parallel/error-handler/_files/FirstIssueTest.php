<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelErrorHandler;

use const E_USER_DEPRECATED;
use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class FirstIssueTest extends TestCase
{
    public function testDeprecation(): void
    {
        trigger_error('deprecation in FirstIssueTest', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testWarning(): void
    {
        trigger_error('warning in FirstIssueTest', E_USER_WARNING);

        $this->assertTrue(true);
    }
}
