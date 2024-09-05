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

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Xml\Loader as XmlLoader;

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

    #[TestDox('Remove cacheDirectory attribute from <coverage> element when migrating from PHPUnit 11.1 to PHPUnit 11.2')]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/5859')]
    public function testIssue5859(): void
    {
        $this->assertEquals(
            (new XmlLoader)->loadFile(__DIR__ . '/../../../../_files/XmlConfigurationMigration/output-5859.xml'),
            (new XmlLoader)->load(
                (new Migrator)->migrate(
                    __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-5859.xml',
                ),
            ),
        );
    }

    #[TestDox('Keep relative schema path when present')]
    public function testKeepRelativeSchema(): void
    {
        $this->assertEquals(
            (new XmlLoader)->loadFile(__DIR__ . '/../../../../_files/XmlConfigurationMigration/output-relative-schema-path.xml'),
            (new XmlLoader)->load(
                (new Migrator)->migrate(
                    __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-relative-schema-path.xml',
                ),
            ),
        );
    }
}
