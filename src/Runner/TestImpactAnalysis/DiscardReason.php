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

/**
 * Why test impact data that is there is not used.
 *
 * A test run that finds nothing it can use runs every test, and one that finds
 * nothing because nothing was recorded is not the same as one that finds
 * something it has to discard: telling the developer that nothing has been
 * recorded when something was would send them looking for a recording that is
 * right in front of them, and not for what made it unusable.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This enumeration is not covered by the backward compatibility promise for PHPUnit
 */
enum DiscardReason
{
    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return match ($this) {
            self::CannotBeRead                        => 'the test impact data that was recorded cannot be read',
            self::RecordedWithAnotherVersionOfPhpunit => 'the test impact data was recorded with another version of PHPUnit',
            self::RecordedWithAnotherVersionOfPhp     => 'the test impact data was recorded with another version of PHP',
            self::ConfigurationFileChanged            => 'the configuration file changed since the test impact data was recorded',
            self::BootstrapScriptChanged              => 'a bootstrap script changed since the test impact data was recorded',
            self::FirstPartyCodeChanged               => 'what is first-party code changed since the test impact data was recorded',
            self::InstalledPackagesChanged            => 'composer.lock changed since the test impact data was recorded',
        };
    }
    case CannotBeRead;
    case RecordedWithAnotherVersionOfPhpunit;
    case RecordedWithAnotherVersionOfPhp;
    case ConfigurationFileChanged;
    case BootstrapScriptChanged;
    case FirstPartyCodeChanged;
    case InstalledPackagesChanged;
}
