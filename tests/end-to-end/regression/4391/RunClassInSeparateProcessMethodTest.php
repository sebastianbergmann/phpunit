<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue4391;

use Exception;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[RunClassInSeparateProcess]
final class RunClassInSeparateProcessMethodTest extends TestCase
{
    #[RequiresPhpunit('< 10')]
    public function testOne(): void
    {
        throw new Exception('message');
    }

    #[RequiresPhpunit('< 10')]
    public function testTwo(): void
    {
        throw new Exception('message');
    }
}
