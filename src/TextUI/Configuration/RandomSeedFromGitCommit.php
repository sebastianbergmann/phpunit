<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function hexdec;
use function substr;
use SebastianBergmann\GitState\Builder as GitStateBuilder;

/**
 * Derives the seed of a test run from the hash of the Git commit that is
 * checked out in the working directory, so that test runs of the same commit,
 * on a continuous integration server, for instance, use the same seed: the
 * number that the first 15 hexadecimal digits of the hash make.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RandomSeedFromGitCommit
{
    /**
     * @return ?positive-int null when no Git commit is checked out in the working directory or Git cannot be run
     */
    public static function seed(): ?int
    {
        $commit = new GitStateBuilder()->commit();

        if ($commit === false) {
            return null;
        }

        return self::fromCommit($commit);
    }

    /**
     * @param non-empty-string $commit the hexadecimal hash of a Git commit
     *
     * @return positive-int
     */
    public static function fromCommit(string $commit): int
    {
        $seed = (int) hexdec(substr($commit, 0, 15));

        if ($seed < 1) {
            return 1;
        }

        return $seed;
    }
}
