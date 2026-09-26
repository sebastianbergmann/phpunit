<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(DiscardReason::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class DiscardReasonTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{0: DiscardReason, 1: non-empty-string}>
     */
    public static function provider(): array
    {
        return [
            'cannot be read'                           => [DiscardReason::CannotBeRead, 'the test impact data that was recorded cannot be read'],
            'recorded with another version of PHPUnit' => [DiscardReason::RecordedWithAnotherVersionOfPhpunit, 'the test impact data was recorded with another version of PHPUnit'],
            'recorded with another version of PHP'     => [DiscardReason::RecordedWithAnotherVersionOfPhp, 'the test impact data was recorded with another version of PHP'],
            'configuration changed'                    => [DiscardReason::ConfigurationChanged, 'the configuration changed since the test impact data was recorded'],
            'bootstrap script changed'                 => [DiscardReason::BootstrapScriptChanged, 'a bootstrap script changed since the test impact data was recorded'],
            'first-party code changed'                 => [DiscardReason::FirstPartyCodeChanged, 'what is first-party code changed since the test impact data was recorded'],
            'installed packages changed'               => [DiscardReason::InstalledPackagesChanged, 'composer.lock changed since the test impact data was recorded'],
        ];
    }

    /**
     * @param non-empty-string $expected
     */
    #[DataProvider('provider')]
    public function testCanBeRepresentedAsString(DiscardReason $reason, string $expected): void
    {
        $this->assertSame($expected, $reason->asString());
    }
}
