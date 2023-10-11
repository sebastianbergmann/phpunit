<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(AtLeastVersionCommand::class)]
#[Small]
final class AtLeastVersionCommandTest extends TestCase
{
    public function testSucceedsWhenRequirementIsMet(): void
    {
        $command = new AtLeastVersionCommand('10');

        $result = $command->execute();

        $this->assertSame('', $result->output());
        $this->assertSame(0, $result->shellExitCode());
    }

    public function testFailsWhenRequirementIsNotMet(): void
    {
        $command = new AtLeastVersionCommand('100');

        $result = $command->execute();

        $this->assertSame('', $result->output());
        $this->assertSame(1, $result->shellExitCode());
    }
}
