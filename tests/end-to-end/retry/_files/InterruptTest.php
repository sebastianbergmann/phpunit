<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Retry;

use const SIGINT;
use function getmypid;
use function posix_kill;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class InterruptTest extends TestCase
{
    #[Retry(3)]
    public function testOne(): void
    {
        posix_kill(getmypid(), SIGINT);

        $this->fail('Failure on first attempt');
    }
}
