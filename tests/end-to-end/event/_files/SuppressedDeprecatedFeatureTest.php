<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class SuppressedDeprecatedFeatureTest extends TestCase
{
    public function testDeprecatedFeature(): void
    {
        // E.g. doctrine/deprecation uses suppressed user deprecation for trigger_error() deprecations, if enabled.
        @trigger_error('message', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }
}
