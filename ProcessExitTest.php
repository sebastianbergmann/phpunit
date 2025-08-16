<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class ProcessExitTest extends TestCase
{
    #[RunInSeparateProcess]
    #[DataProvider("provideExitCodes")]
    public function testOne(?int $expectedExit, int $actualExitCode): void
    {
        if ($expectedExit !== null) {
            $this->expectProcessExit($expectedExit);
        }

        exit($actualExitCode);
    }

    static public function provideExitCodes():iterable {
        yield [null, 0];
        yield [null, 1];

        yield [0, 0];
        yield [0, 1];

        yield [1, 1];
        yield [1, 0];
    }
}
