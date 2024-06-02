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

use function ob_clean;
use function ob_end_clean;
use function ob_flush;
use function ob_start;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class Issue5851Test extends TestCase
{
    public function testInvalidFlushBuffer(): void
    {
        $this->expectOutputString('hello');

        print 'hello';

        ob_flush();
    }

    public function testInvalidSilencedFlushBuffer(): void
    {
        $this->expectOutputString('hello');

        print 'hello';

        @ob_flush();
    }

    public function testInvalidFlushBufferEmpty(): void
    {
        $this->expectOutputString('');

        ob_flush();
    }

    public function testInvalidCleanExternalBuffer(): void
    {
        print 'Illegaly hide this';

        $this->assertTrue(ob_clean());
    }

    public function testRemovedAndAddedBufferNoOutput(): void
    {
        $this->expectOutputString('');

        ob_end_clean();

        ob_start();
    }

    public function testRemovedAndAddedBufferOutput(): void
    {
        $this->expectOutputString('');

        ob_end_clean();

        print 'Sneaky';

        ob_start();
    }

    public function testRemovedAndAddedBufferExpectedOutput(): void
    {
        $this->expectOutputString('Safe');

        ob_end_clean();

        print 'Naughty';

        ob_start();

        print 'Safe';
    }

    public function testNonClosedBufferShouldntBeIgnored(): void
    {
        $this->expectOutputString('');

        ob_start();
        ob_start();
        ob_start();
        print 'Do not ignore this';
        ob_start();
        print 'or this';
    }

    public function testNonClosedBufferShouldntBeIgnored2(): void
    {
        $this->expectOutputString('Do not ignore thisor this');

        ob_start();
        ob_start();
        ob_start();
        print 'Do not ignore this';
        ob_start();
        print 'or this';
    }

    #[RunInSeparateProcess]
    public function testNonRemovableBufferSeparateProcess(): void
    {
        $this->expectOutputString('helloworldbye');

        $this->runNonRemovableBuffer([$this, 'bufferCallbackA'], 0, 0);
    }

    public function testNonRemovableBuffer(): void
    {
        $this->expectOutputString('helloworldbye');

        $this->runNonRemovableBuffer([$this, 'bufferCallbackB'], 0, 0);
    }

    /**
     * check that the previous buffer that hasn't been ended won't break this.
     */
    #[RunInSeparateProcess]
    public function testNonRemovableBufferSeparateProcessAgain(): void
    {
        $this->expectOutputString('helloworldbye');

        $this->runNonRemovableBuffer([$this, 'bufferCallbackC'], 0, 0);
    }

    #[RunInSeparateProcess]
    public function testNonRemovableBufferChunkSizeTooLow(): void
    {
        $this->expectOutputString('helloworldbye');

        $this->runNonRemovableBuffer([$this, 'bufferCallbackC'], 1, 0);
    }

    #[RunInSeparateProcess]
    public function testEmptyNonRemovableBufferSeparateProcess(): void
    {
        $this->expectOutputString('');

        ob_start(null, 0, 0);
    }

    public function runNonRemovableBuffer(?callable $callable, int $chunk = 0, int $flags = 0): void
    {
        ob_start($callable, $chunk, $flags);
        print 'hello';
        print 'world';
        print 'bye';
    }

    private function bufferCallbackA(string $output): string
    {
        return $output;
    }

    private function bufferCallbackB(string $output): string
    {
        return $output;
    }

    private function bufferCallbackC(string $output): string
    {
        return $output;
    }
}
