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

use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class RetryInSeparateProcessAllAttemptsFailTest extends TestCase
{
    #[Retry(2)]
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        $this->fail('Failure in child process');
    }
}
