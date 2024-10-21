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

use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Http\Downloader;

#[CoversClass(VersionCheckCommand::class)]
#[Small]
final class VersionCheckCommandTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-negative-int, 2: positive-int, 3: non-empty-string, 4: non-empty-string, 5: non-empty-string}>
     */
    public static function provider(): array
    {
        return [
            [
                'You are using the latest version of PHPUnit.' . PHP_EOL,
                Result::SUCCESS,
                10,
                '10.5.0',
                '10.5.0',
                '10.5.0',
            ],
            [
                'You are not using the latest version of PHPUnit.' . PHP_EOL .
                'The latest version compatible with PHPUnit 10.5.0 is PHPUnit 10.5.1.' . PHP_EOL .
                'The latest version is PHPUnit 10.5.1.' . PHP_EOL,
                Result::FAILURE,
                10,
                '10.5.0',
                '10.5.1',
                '10.5.1',
            ],
            [
                'You are not using the latest version of PHPUnit.' . PHP_EOL .
                'The latest version compatible with PHPUnit 10.5.0 is PHPUnit 10.5.1.' . PHP_EOL .
                'The latest version is PHPUnit 11.0.0.' . PHP_EOL,
                Result::FAILURE,
                10,
                '10.5.0',
                '11.0.0',
                '10.5.1',
            ],
        ];
    }

    /**
     * @param non-empty-string $expectedMessage
     * @param non-negative-int $expectedShellExitCode
     * @param positive-int     $majorVersionNumber
     * @param non-empty-string $versionId
     * @param non-empty-string $latestVersion
     * @param non-empty-string $latestCompatibleVersion
     */
    #[DataProvider('provider')]
    public function testChecksVersion(string $expectedMessage, int $expectedShellExitCode, int $majorVersionNumber, string $versionId, string $latestVersion, string $latestCompatibleVersion): void
    {
        $command = new VersionCheckCommand(
            $this->downloader($latestVersion, $latestCompatibleVersion),
            $majorVersionNumber,
            $versionId,
        );

        $result = $command->execute();

        $this->assertSame($expectedMessage, $result->output());
        $this->assertSame($expectedShellExitCode, $result->shellExitCode());
    }

    private function downloader(string $latestVersion, string $latestCompatibleVersion): Downloader&Stub
    {
        $downloader = $this->createStub(Downloader::class);

        $downloader
            ->method('download')
            ->willReturn($latestVersion, $latestCompatibleVersion);

        return $downloader;
    }
}
