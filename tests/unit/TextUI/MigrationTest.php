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

use PHPUnit\Framework\TestCase;

final class MigrationTest extends TestCase
{
    /**
     * @testdox Migrates PHPUnit 9.2 configuration to PHPUnit 9.3
     */
    public function testMigratesPhpUnit92ConfigurationToPhpUnit93(): void
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/../../_files/XmlConfigurationMigration/output-9.3.xml',
            (new Migrator)->migrate(
                __DIR__ . '/../../_files/XmlConfigurationMigration/input-9.2.xml'
            )
        );
    }
}
