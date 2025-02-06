<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsExternalUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsExternalUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsOnClassUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsOnClassUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\Attributes\ExcludeStaticPropertyFromBackup;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RequiresPhpunitExtension;
use PHPUnit\Framework\Attributes\RequiresSetting;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\TestWithJson;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use PHPUnit\Framework\Attributes\UsesMethod;
use PHPUnit\Framework\Attributes\UsesTrait;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Metadata\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Metadata\InvalidAttributeException;

#[CoversClass(AttributeParser::class)]
#[CoversClass(AfterClass::class)]
#[CoversClass(After::class)]
#[CoversClass(BackupGlobals::class)]
#[CoversClass(BackupStaticProperties::class)]
#[CoversClass(BeforeClass::class)]
#[CoversClass(Before::class)]
#[CoversClass(CoversClass::class)]
#[CoversClass(CoversFunction::class)]
#[CoversClass(CoversMethod::class)]
#[CoversClass(CoversNothing::class)]
#[CoversClass(CoversTrait::class)]
#[CoversClass(DataProviderExternal::class)]
#[CoversClass(DataProvider::class)]
#[CoversClass(DependsExternal::class)]
#[CoversClass(DependsExternalUsingDeepClone::class)]
#[CoversClass(DependsExternalUsingShallowClone::class)]
#[CoversClass(DependsOnClass::class)]
#[CoversClass(DependsOnClassUsingDeepClone::class)]
#[CoversClass(DependsOnClassUsingShallowClone::class)]
#[CoversClass(Depends::class)]
#[CoversClass(DependsUsingDeepClone::class)]
#[CoversClass(DependsUsingShallowClone::class)]
#[CoversClass(DisableReturnValueGenerationForTestDoubles::class)]
#[CoversClass(DoesNotPerformAssertions::class)]
#[CoversClass(ExcludeGlobalVariableFromBackup::class)]
#[CoversClass(ExcludeStaticPropertyFromBackup::class)]
#[CoversClass(Group::class)]
#[CoversClass(InvalidAttributeException::class)]
#[CoversClass(Large::class)]
#[CoversClass(Medium::class)]
#[CoversClass(PostCondition::class)]
#[CoversClass(PreCondition::class)]
#[CoversClass(PreserveGlobalState::class)]
#[CoversClass(RequiresFunction::class)]
#[CoversClass(RequiresMethod::class)]
#[CoversClass(RequiresOperatingSystemFamily::class)]
#[CoversClass(RequiresOperatingSystem::class)]
#[CoversClass(RequiresPhpExtension::class)]
#[CoversClass(RequiresPhp::class)]
#[CoversClass(RequiresPhpunit::class)]
#[CoversClass(RequiresPhpunitExtension::class)]
#[CoversClass(RequiresSetting::class)]
#[CoversClass(RunClassInSeparateProcess::class)]
#[CoversClass(RunInSeparateProcess::class)]
#[CoversClass(RunTestsInSeparateProcesses::class)]
#[CoversClass(Small::class)]
#[CoversClass(TestDox::class)]
#[CoversClass(Test::class)]
#[CoversClass(TestWithJson::class)]
#[CoversClass(TestWith::class)]
#[CoversClass(Ticket::class)]
#[CoversClass(UsesClass::class)]
#[CoversClass(UsesFunction::class)]
#[CoversClass(UsesMethod::class)]
#[CoversClass(UsesTrait::class)]
#[CoversClass(WithoutErrorHandler::class)]
#[Small]
#[Group('metadata')]
#[Group('metadata/attributes')]
final class AttributeParserTest extends AttributeParserTestCase
{
    protected function parser(): Parser
    {
        return new AttributeParser;
    }
}
