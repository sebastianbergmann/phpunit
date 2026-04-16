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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

#[CoversClass(ValidateConfigurationCommand::class)]
#[Small]
#[Group('textui')]
#[Group('textui/commands')]
final class ValidateConfigurationCommandTest extends TestCase
{
    public function testSucceedsForValidConfigurationFile(): void
    {
        $command = new ValidateConfigurationCommand(
            __DIR__ . '/../../../../end-to-end/cli/validate-configuration/_files/valid/phpunit.xml',
        );

        $result = $command->execute();

        $this->assertSame(Result::SUCCESS, $result->shellExitCode());
        $this->assertStringContainsString('is valid', $result->output());
    }

    public function testFailsForInvalidConfigurationFile(): void
    {
        $command = new ValidateConfigurationCommand(
            __DIR__ . '/../../../../end-to-end/cli/validate-configuration/_files/invalid/phpunit.xml',
        );

        $result = $command->execute();

        $this->assertSame(Result::FAILURE, $result->shellExitCode());
        $this->assertStringContainsString(
            'does not validate against the PHPUnit ' . Version::series() . ' schema:',
            $result->output(),
        );
    }

    public function testFailsForNonExistentConfigurationFile(): void
    {
        $command = new ValidateConfigurationCommand(
            __DIR__ . '/does-not-exist.xml',
        );

        $result = $command->execute();

        $this->assertSame(Result::FAILURE, $result->shellExitCode());
        $this->assertStringContainsString('Cannot load XML configuration file', $result->output());
    }
}
