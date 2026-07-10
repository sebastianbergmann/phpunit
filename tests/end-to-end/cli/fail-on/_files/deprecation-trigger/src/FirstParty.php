<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\FailOnDeprecationTrigger;

use const E_USER_DEPRECATED;
use function trigger_error;

require_once __DIR__ . '/../vendor/third-party.php';

final class FirstParty
{
    public function triggerDeprecation(): void
    {
        trigger_error('deprecation triggered in first-party code', E_USER_DEPRECATED);
    }

    public function callThirdParty(): void
    {
        phpunit_test_fixture_fail_on_deprecation_trigger_third_party();
    }

    public function callThirdPartyThatCallsThirdParty(): void
    {
        phpunit_test_fixture_fail_on_deprecation_trigger_third_party_calling_third_party();
    }
}
