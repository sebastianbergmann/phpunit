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

/**
 * @small
 * @covers \PHPUnit\TextUI\XmlConfiguration\Generator
 */
final class XmlConfigurationGeneratorTest extends TestCase
{
    public function testGeneratesConfigurationCorrectly(): void
    {
        $generator = new Generator;

        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/X.Y.Z/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         cacheDirectory=".phpunit.cache"
         executionOrder="depends,defects"
         forceCoversAnnotation="true"
         beStrictAboutCoversAnnotation="true"
         beStrictAboutOutputDuringTests="true"
         failOnRisky="true"
         failOnWarning="true"
         verbose="true">
    <testsuites>
        <testsuite name="default">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
',
            $generator->generateDefaultConfiguration(
                'X.Y.Z',
                'vendor/autoload.php',
                'tests',
                'src',
                '.phpunit.cache'
            )
        );
    }
}
