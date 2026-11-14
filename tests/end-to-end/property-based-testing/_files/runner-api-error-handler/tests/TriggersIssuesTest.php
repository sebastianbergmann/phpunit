<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use const E_USER_DEPRECATED;
use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class TriggersIssuesTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integersWhoseGenerationTriggersAWarning(): ValueGenerator
    {
        return Generators::integers(0, 9)->map(
            static function (int $value): int
            {
                trigger_error('triggered while a value is generated', E_USER_WARNING);

                return $value;
            },
        );
    }

    public static function setUpBeforeClass(): void
    {
        @trigger_error('triggered in setUpBeforeClass()', E_USER_WARNING);
    }

    public static function tearDownAfterClass(): void
    {
        @trigger_error('triggered in tearDownAfterClass()', E_USER_WARNING);
    }

    protected function setUp(): void
    {
        trigger_error('triggered in setUp()', E_USER_WARNING);
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testTriggersAWarning(int $value): void
    {
        trigger_error('triggered in a test method', E_USER_WARNING);

        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testTriggersADeprecation(int $value): void
    {
        trigger_error('triggered in a test method', E_USER_DEPRECATED);

        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testTriggersASuppressedWarning(int $value): void
    {
        @trigger_error('triggered in a test method', E_USER_WARNING);

        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    public function testTriggersAWarningWhileItsArgumentsAreGenerated(#[ForAll('integersWhoseGenerationTriggersAWarning')] int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
