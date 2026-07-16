<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Job::class)]
#[Small]
final class JobTest extends TestCase
{
    public function testHasCode(): void
    {
        $code = 'the-code';

        $job = new Job(
            $code,
            ChildProcessReason::TestRequiringProcessIsolation,
            [],
            [],
            [],
            null,
            false,
        );

        $this->assertSame($code, $job->code());

        $this->assertFalse($job->hasEnvironmentVariables());
        $this->assertFalse($job->hasInput());
        $this->assertFalse($job->redirectErrors());
    }

    public function testHasReason(): void
    {
        $reason = ChildProcessReason::PhptTest;

        $job = new Job(
            'the-code',
            $reason,
            [],
            [],
            [],
            null,
            false,
        );

        $this->assertSame($reason, $job->reason());
    }

    public function testMayHavePhpSettings(): void
    {
        $phpSettings = ['foo' => 'bar'];

        $job = new Job(
            'the-code',
            ChildProcessReason::TestRequiringProcessIsolation,
            $phpSettings,
            [],
            [],
            null,
            false,
        );

        $this->assertSame($phpSettings, $job->phpSettings());

        $this->assertFalse($job->hasEnvironmentVariables());
        $this->assertFalse($job->hasInput());
        $this->assertFalse($job->redirectErrors());
    }

    public function testMayHaveEnvironmentVariables(): void
    {
        $environmentVariables = ['foo' => 'bar'];

        $job = new Job(
            'the-code',
            ChildProcessReason::TestRequiringProcessIsolation,
            [],
            $environmentVariables,
            [],
            null,
            false,
        );

        $this->assertTrue($job->hasEnvironmentVariables());
        $this->assertSame($environmentVariables, $job->environmentVariables());

        $this->assertFalse($job->hasInput());
        $this->assertFalse($job->redirectErrors());
    }

    public function testMayHaveArguments(): void
    {
        $arguments = ['foo', 'bar'];

        $job = new Job(
            'the-code',
            ChildProcessReason::TestRequiringProcessIsolation,
            [],
            [],
            $arguments,
            null,
            false,
        );

        $this->assertTrue($job->hasArguments());
        $this->assertSame($arguments, $job->arguments());

        $this->assertFalse($job->hasEnvironmentVariables());
        $this->assertFalse($job->hasInput());
        $this->assertFalse($job->redirectErrors());
    }

    public function testMayHaveInput(): void
    {
        $input = 'the-input';

        $job = new Job(
            'the-code',
            ChildProcessReason::TestRequiringProcessIsolation,
            [],
            [],
            [],
            $input,
            false,
        );

        $this->assertTrue($job->hasInput());
        $this->assertSame($input, $job->input());

        $this->assertFalse($job->hasEnvironmentVariables());
        $this->assertFalse($job->redirectErrors());
    }

    public function testMayNotHaveInput(): void
    {
        $job = new Job(
            'the-code',
            ChildProcessReason::TestRequiringProcessIsolation,
            [],
            [],
            [],
            null,
            false,
        );

        $this->assertFalse($job->hasInput());

        $this->expectException(PhpProcessException::class);

        $job->input();
    }

    public function testMayRedirectErrors(): void
    {
        $job = new Job(
            'the-code',
            ChildProcessReason::TestRequiringProcessIsolation,
            [],
            [],
            [],
            null,
            true,
        );

        $this->assertTrue($job->redirectErrors());
    }
}
