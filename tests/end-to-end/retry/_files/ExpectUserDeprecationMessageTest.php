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

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class ExpectUserDeprecationMessageTest extends TestCase
{
    #[IgnoreDeprecations]
    #[Retry(2)]
    public function testExpectationIsMetByDeprecationTriggeredInAttempt(): void
    {
        $this->expectUserDeprecationMessage('deprecation');

        trigger_error('deprecation', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    #[Retry(2)]
    public function testExpectationIsNotMetByDeprecationTriggeredInPreviousTest(): void
    {
        $this->expectUserDeprecationMessage('deprecation');
    }
}
