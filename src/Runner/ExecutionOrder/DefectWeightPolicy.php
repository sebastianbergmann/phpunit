<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder;

use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * Decides how much a test status weighs when tests are ordered "defects first".
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface DefectWeightPolicy
{
    /**
     * A weight greater than zero hoists the test towards the front of the
     * test suite; tests of equal weight keep their relative order.
     *
     * @return non-negative-int
     */
    public function weight(TestStatus $status): int;
}
