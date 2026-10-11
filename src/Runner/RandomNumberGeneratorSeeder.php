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

use const PHP_INT_MAX;
use function assert;
use function extension_loaded;
use function gmp_random_seed;
use function hash;
use function is_int;
use function mt_srand;
use function unpack;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Seeds the random number generators whose state is global to the PHP process:
 * the Mt19937 random number generator that mt_rand(), rand(), shuffle(),
 * str_shuffle(), and array_rand() use, and the random number generator of the
 * gmp extension.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RandomNumberGeneratorSeeder
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Seeds the random number generators with the seed of the test run.
     */
    public function seed(): void
    {
        if (!$this->configuration->seedRandomNumberGenerators()) {
            return;
        }

        $this->seedWith($this->configuration->randomSeed());
    }

    /**
     * Seeds the random number generators with a seed that is derived from the
     * seed of the test run and the given ID, so that what is run for this ID
     * gets the same random numbers regardless of filtering, execution order,
     * process isolation, and what was run before it.
     */
    public function seedFor(string $id): void
    {
        if (!$this->configuration->seedRandomNumberGenerators()) {
            return;
        }

        $this->seedWith($this->derive($this->configuration->randomSeed(), $id));
    }

    /**
     * @return non-negative-int
     */
    private function derive(int $seedOfTestRun, string $id): int
    {
        $hash = unpack('J', hash('xxh64', $seedOfTestRun . "\0" . $id, true));

        assert($hash !== false && isset($hash[1]) && is_int($hash[1]));

        $seed = $hash[1] & PHP_INT_MAX;

        assert($seed >= 0);

        return $seed;
    }

    private function seedWith(int $seed): void
    {
        mt_srand($seed);

        if (extension_loaded('gmp')) {
            gmp_random_seed($seed);
        }
    }
}
