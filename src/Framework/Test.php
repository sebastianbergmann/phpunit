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

use Countable;
use PHPUnit\TextUI\TestRunner;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface Test extends Countable
{
    /**
     * Runs a test and collects its result in a TestResult instance.
     */
    public function run(TestResult $result = null, TestRunner $runner = null): TestResult;
}
