<?php

declare(strict_types=1);

namespace PHPUnit\TestFixture\CodeCoverage;

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


function fixture_for_phpunit_code_coverage(): bool
{
    return true;
}


function fixture_for_phpunit_code_coverage_not_called(): bool
{
    return false;
}
