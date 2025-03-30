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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Xml\Loader as XmlLoader;

final class MigratorTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'PHPUnit 9.2' => [
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/output-9.2.xml',
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-9.2.xml',
            ],
            'PHPUnit 9.5' => [
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/output-9.5.xml',
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-9.5.xml',
            ],
            'Relative Path' => [
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/output-relative-schema-path.xml',
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-relative-schema-path.xml',
            ],
            'Issue 5859' => [
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/output-issue-5859.xml',
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-issue-5859.xml',
            ],
            'Issue 6087' => [
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/output-issue-6087.xml',
                __DIR__ . '/../../../../_files/XmlConfigurationMigration/input-issue-6087.xml',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanMigrateConfigurationFileThatValidatesAgainstPreviousSchema(string $output, string $input): void
    {
        $this->assertEquals(
            (new XmlLoader)->loadFile($output),
            (new XmlLoader)->load((new Migrator)->migrate($input)),
        );
    }
}
