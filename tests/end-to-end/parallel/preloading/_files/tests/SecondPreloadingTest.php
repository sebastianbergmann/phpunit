<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelPreloading;

use function class_exists;
use PHPUnit\Framework\Constraint\IsNan;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CliParser\UnknownOptionException;

final class SecondPreloadingTest extends TestCase
{
    public function testCodeOfPhpunitThatTheTestRunnerDidNotUseIsLoadedBeforeATestIsRun(): void
    {
        $this->assertTrue(class_exists(IsNan::class, false));
    }

    public function testCodeOfADependencyOfPhpunitThatTheTestRunnerDidNotUseIsLoadedBeforeATestIsRun(): void
    {
        $this->assertTrue(class_exists(UnknownOptionException::class, false));
    }
}
