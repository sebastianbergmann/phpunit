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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once __DIR__ . '/MachineWithoutRules.php';

final class AbortedTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integersThatCannotBeGenerated(): ValueGenerator
    {
        return Generators::integers(0, 9)->map(
            static function (int $value): int
            {
                throw new RuntimeException('cannot be generated');
            },
        );
    }

    /**
     * @return ValueGenerator<int>
     */
    public static function generatorMethodThatRaisesAnException(): ValueGenerator
    {
        throw new RuntimeException('no generator');
    }

    protected function setUp(): void
    {
        if ($this->name() === 'testCannotBeSetUp') {
            throw new RuntimeException('cannot be set up');
        }
    }

    #[Property]
    public function testArgumentsCannotBeGenerated(#[ForAll('integersThatCannotBeGenerated')] int $value): void
    {
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testCannotBeSetUp(int $value): void
    {
    }

    #[Property]
    public function testRunsAnInvalidStateMachine(): void
    {
        $this->runStateMachine(new MachineWithoutRules);
    }

    #[Property]
    public function testHasAGeneratorMethodThatRaisesAnException(#[ForAll('generatorMethodThatRaisesAnException')] int $value): void
    {
    }
}
