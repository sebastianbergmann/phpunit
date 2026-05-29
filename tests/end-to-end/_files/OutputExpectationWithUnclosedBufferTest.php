<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function ob_start;
use PHPUnit\Framework\TestCase;

final class OutputExpectationWithUnclosedBufferTest extends TestCase
{
    public function testOutputExpectationIsReportedAsRiskyWhenBufferIsLeftOpen(): void
    {
        $this->expectOutputString('hello');
        $this->assertTrue(true);

        print 'hello';

        ob_start();
    }
}
