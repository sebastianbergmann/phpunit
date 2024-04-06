<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ThrowExceptionTestCase extends TestCase
{
    public function test(): void
    {
        throw new RuntimeException('A runtime error occurred');
    }

    public function testWithExpectExceptionObject(): void
    {
        throw new RuntimeException(
            'Cannot compute at this time.',
            9000,
        );
    }
}
