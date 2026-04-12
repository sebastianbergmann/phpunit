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

use const PHP_OUTPUT_HANDLER_FLUSHABLE;
use function ob_clean;
use function ob_end_clean;
use function ob_flush;
use function ob_start;
use PHPUnit\Framework\TestCase;

final class Issue5851Test extends TestCase
{
    public function testOutputCapturedAfterObClean(): void
    {
        $this->expectOutputString('hello');

        print 'hel';
        ob_clean();
        print 'hello';
    }

    public function testOutputCapturedAfterObFlush(): void
    {
        $this->expectOutputString('hello');

        print 'hello';
        ob_flush();
    }

    public function testBufferSubstitutionDetected(): void
    {
        ob_end_clean();
        ob_start();

        $this->assertTrue(true);
    }

    public function testNonRemovableBufferDoesNotCauseInfiniteLoop(): void
    {
        ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE);

        $this->assertTrue(true);
    }
}
