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

use function ob_end_clean;
use function ob_start;
use PHPUnit\Framework\TestCase;

final class ResumedOutputBufferingWithUnclosedBufferTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);

        ob_start();
    }

    protected function invokeTestMethod(string $methodName, array $testArguments): mixed
    {
        $this->suspendOutputBuffering();

        // simulates a coroutine runtime that runs the test method with its own output buffers
        ob_start();

        try {
            $this->resumeOutputBuffering();

            try {
                return parent::invokeTestMethod($methodName, $testArguments);
            } finally {
                $this->suspendOutputBuffering();
            }
        } finally {
            ob_end_clean();

            $this->resumeOutputBuffering();
        }
    }
}
