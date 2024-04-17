<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Xml\Loader as XmlLoader;

#[CoversClass(Migrator::class)]
#[CoversClass(MigrationBuilder::class)]
#[CoversClass(RemoveLogTypes::class)]
#[CoversClass(RemoveCacheTokensAttribute::class)]
#[CoversClass(IntroduceCoverageElement::class)]
#[CoversClass(MoveAttributesFromRootToCoverage::class)]
#[CoversClass(MoveAttributesFromFilterWhitelistToCoverage::class)]
#[CoversClass(MoveWhitelistIncludesToCoverage::class)]
#[CoversClass(MoveWhitelistExcludesToCoverage::class)]
#[CoversClass(RemoveEmptyFilter::class)]
#[CoversClass(CoverageCloverToReport::class)]
#[CoversClass(CoverageCrap4jToReport::class)]
#[CoversClass(CoverageHtmlToReport::class)]
#[CoversClass(CoveragePhpToReport::class)]
#[CoversClass(CoverageTextToReport::class)]
#[CoversClass(CoverageXmlToReport::class)]
#[CoversClass(ConvertLogTypes::class)]
#[CoversClass(RemoveListeners::class)]
#[CoversClass(RemoveTestSuiteLoaderAttributes::class)]
#[CoversClass(RemoveCacheResultFileAttribute::class)]
#[CoversClass(RemoveCoverageElementCacheDirectoryAttribute::class)]
#[CoversClass(RemoveCoverageElementProcessUncoveredFilesAttribute::class)]
#[CoversClass(IntroduceCacheDirectoryAttribute::class)]
#[CoversClass(RenameBackupStaticAttributesAttribute::class)]
#[CoversClass(RemoveBeStrictAboutResourceUsageDuringSmallTestsAttribute::class)]
#[CoversClass(RemoveBeStrictAboutTodoAnnotatedTestsAttribute::class)]
#[CoversClass(RemovePrinterAttributes::class)]
#[CoversClass(RemoveVerboseAttribute::class)]
#[CoversClass(RenameForceCoversAnnotationAttribute::class)]
#[CoversClass(RenameBeStrictAboutCoversAnnotationAttribute::class)]
#[CoversClass(RemoveConversionToExceptionsAttributes::class)]
#[CoversClass(RemoveNoInteractionAttribute::class)]
#[CoversClass(RemoveLoggingElements::class)]
#[CoversClass(RemoveTestDoxGroupsElement::class)]
#[CoversClass(MoveCoverageDirectoriesToSource::class)]
#[CoversClass(RemoveRegisterMockObjectsFromTestArgumentsRecursivelyAttribute::class)]
#[CoversClass(ReplaceRestrictDeprecationsWithIgnoreDeprecations::class)]
#[Medium]
final class MigratorTest extends TestCase
{
    #[TestDox('Can migrate PHPUnit 9.2 configuration')]
    public function testCanMigratePhpUnit92Configuration(): void
    {
        $this->assertEquals(
            (new XmlLoader)->loadFile(__DIR__ . '/../../../../_files/XmlConfigurationMigration/output-9.2.xml'),
            (new XmlLoader)->load(
                (new Migrator)->migrate(
                    __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-9.2.xml',
                ),
            ),
        );
    }

    #[TestDox('Can migrate PHPUnit 9.5 configuration')]
    public function testCanMigratePhpUnit95Configuration(): void
    {
        $this->assertEquals(
            (new XmlLoader)->loadFile(__DIR__ . '/../../../../_files/XmlConfigurationMigration/output-9.5.xml'),
            (new XmlLoader)->load(
                (new Migrator)->migrate(
                    __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-9.5.xml',
                ),
            ),
        );
    }
}
