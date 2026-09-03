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
 * Only errors and failures are considered defects; a test that was skipped or
 * marked incomplete, or that triggered an issue, is not hoisted to the front.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DefaultDefectWeightPolicy implements DefectWeightPolicy
{
    /**
     * @return non-negative-int
     */
    public function weight(TestStatus $status): int
    {
        if ($status->isError()) {
            return 2;
        }

        if ($status->isFailure()) {
            return 1;
        }

        return 0;
    }
}
