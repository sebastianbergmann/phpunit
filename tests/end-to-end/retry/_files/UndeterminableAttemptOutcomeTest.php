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
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;

final class UndeterminableAttemptOutcomeTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->setStatus(TestStatus::failure('failure that did not emit an event'));
    }

    #[Retry(3)]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
