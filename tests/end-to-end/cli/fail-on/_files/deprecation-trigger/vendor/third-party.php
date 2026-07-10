<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
function phpunit_test_fixture_fail_on_deprecation_trigger_third_party(): void
{
    \trigger_error('deprecation triggered in third-party code', \E_USER_DEPRECATED);
}

function phpunit_test_fixture_fail_on_deprecation_trigger_third_party_calling_third_party(): void
{
    phpunit_test_fixture_fail_on_deprecation_trigger_third_party();
}
