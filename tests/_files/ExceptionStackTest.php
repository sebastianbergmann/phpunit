<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ExceptionStackTest extends TestCase
{
    public function testPrintingChildException(): void
    {
        try {
            $this->assertEquals([1], [2], 'message');
        } catch (ExpectationFailedException $e) {
            $message = $e->getMessage() . $e->getComparisonFailure()->getDiff();

            throw new PHPUnit\Framework\Exception("Child exception\n$message", 101, $e);
        }
    }

    public function testNestedExceptions(): void
    {
        $exceptionThree = new Exception('Three');
        $exceptionTwo   = new InvalidArgumentException('Two', 0, $exceptionThree);
        $exceptionOne   = new Exception('One', 0, $exceptionTwo);

        throw $exceptionOne;
    }
}
