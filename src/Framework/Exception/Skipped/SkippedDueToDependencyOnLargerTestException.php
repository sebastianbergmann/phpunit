<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SkippedDueToDependencyOnLargerTestException extends AssertionFailedError implements SkippedTest
{
    public function __construct()
    {
        parent::__construct('This test depends on a test that is larger than itself');
    }
}
