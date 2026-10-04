<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue7029;

use function array_column;
use function array_merge;
use function is_array;
use Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

final class Issue7029Test extends TestCase
{
    public function testStackTraceOfExceptionDoesNotReferenceTestsThatHaveNotRunYet(): void
    {
        $trace = (new Exception)->getTrace();

        $this->assertNotSame([], array_merge(...array_column($trace, 'args')));

        $framesThatReferenceTests = [];

        foreach ($trace as $frame) {
            foreach ($frame['args'] as $argument) {
                if (!is_array($argument)) {
                    continue;
                }

                foreach ($argument as $element) {
                    if ($element instanceof Test) {
                        $framesThatReferenceTests[] = $frame['class'] . '::' . $frame['function'] . '()';

                        continue 3;
                    }
                }
            }
        }

        $this->assertSame([], $framesThatReferenceTests);
    }

    public function testThatHasNotRunYet(): void
    {
        $this->assertTrue(true);
    }
}
