<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

use function chdir;
use PHPUnit\Framework\TestCase;

final class RegistersFixturesTest extends TestCase
{
    public function testRegistersAFileAndADirectory(): void
    {
        $this->registerFixture(__DIR__ . '/../fixtures/sums.csv', __DIR__ . '/../fixture-directory');

        $this->assertSame(3, (new Calculator)->add(1, 2));
    }

    public function testRegistersAPathThatIsRelativeToTheWorkingDirectory(): void
    {
        chdir(__DIR__ . '/../fixtures');

        $this->registerFixture('sums.csv');

        $this->assertSame(3, (new Calculator)->add(1, 2));
    }

    public function testRegistersAFileTheTestAlsoExecuted(): void
    {
        $this->registerFixture(__DIR__ . '/../src/Calculator.php');

        $this->assertSame(3, (new Calculator)->add(1, 2));
    }

    public function testRegistersPathsThatDoNotExist(): void
    {
        $this->registerFixture('does-not-exist.csv', '', __DIR__ . '/../fixtures/sums.csv');

        $this->assertSame(3, (new Calculator)->add(1, 2));
    }

    public function testRegistersAFixtureAndIsSkipped(): void
    {
        $this->registerFixture(__DIR__ . '/../fixtures/sums.csv');

        $this->markTestSkipped('a skipped test is not recorded');
    }
}
