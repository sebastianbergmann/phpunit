<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function gmp_random_bits;
use function gmp_random_seed;
use function gmp_strval;
use function mt_rand;
use function mt_srand;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(RandomNumberGeneratorSeeder::class)]
#[Medium]
final class RandomNumberGeneratorSeederTest extends TestCase
{
    protected function tearDown(): void
    {
        mt_srand();
    }

    public function testSeedsMt19937WithSeedOfTestRun(): void
    {
        new RandomNumberGeneratorSeeder($this->configuration(['--random-seed', '1234']))->seed();

        $actual = mt_rand();

        mt_srand(1234);

        $this->assertSame(mt_rand(), $actual);
    }

    public function testSeedsMt19937WithSeedDerivedFromSeedOfTestRunAndId(): void
    {
        $seeder = new RandomNumberGeneratorSeeder($this->configuration(['--random-seed', '1234']));

        $seeder->seedFor('id');

        $expected = [mt_rand(), mt_rand()];

        mt_srand(4321);

        $seeder->seedFor('id');

        $this->assertSame($expected, [mt_rand(), mt_rand()]);
    }

    public function testSeedThatIsDerivedForDifferentIdsIsDifferent(): void
    {
        $seeder = new RandomNumberGeneratorSeeder($this->configuration(['--random-seed', '1234']));

        $seeder->seedFor('one');

        $one = mt_rand();

        $seeder->seedFor('two');

        $this->assertNotSame($one, mt_rand());
    }

    public function testSeedThatIsDerivedForDifferentSeedsOfTestRunIsDifferent(): void
    {
        new RandomNumberGeneratorSeeder($this->configuration(['--random-seed', '1234']))->seedFor('id');

        $one = mt_rand();

        new RandomNumberGeneratorSeeder($this->configuration(['--random-seed', '4321']))->seedFor('id');

        $this->assertNotSame($one, mt_rand());
    }

    public function testDerivesTheSameSeedOnEveryPlatformAndWithEveryVersion(): void
    {
        // a seed that is printed by one test run reproduces it on another
        // machine only as long as the derivation does not change
        $this->assertSame(7643959643694676917, RandomNumberGeneratorSeeder::derive(1234, 'ExampleTest::testOne'));
    }

    #[RequiresPhpExtension('gmp')]
    public function testSeedsRandomNumberGeneratorOfGmpExtension(): void
    {
        $seeder = new RandomNumberGeneratorSeeder($this->configuration(['--random-seed', '1234']));

        $seeder->seedFor('id');

        $expected = gmp_strval(gmp_random_bits(64));

        gmp_random_seed(4321);

        $seeder->seedFor('id');

        $this->assertSame($expected, gmp_strval(gmp_random_bits(64)));
    }

    public function testDoesNotSeedWhenRandomNumberGeneratorsAreNotSeeded(): void
    {
        $seeder = new RandomNumberGeneratorSeeder($this->configuration([]));

        mt_srand(4321);

        $seeder->seed();
        $seeder->seedFor('id');

        $actual = mt_rand();

        mt_srand(4321);

        $this->assertSame(mt_rand(), $actual);
    }

    /**
     * @param list<string> $parameters
     */
    private function configuration(array $parameters): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            new Builder($this->createStub(Emitter::class))->fromParameters($parameters),
            DefaultConfiguration::create(),
        );
    }
}
