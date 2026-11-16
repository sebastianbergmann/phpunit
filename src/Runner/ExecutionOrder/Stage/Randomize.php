<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder\Stage;

use PHPUnit\Framework\Test;
use PHPUnit\Runner\ExecutionOrder\Context;
use PHPUnit\Runner\ExecutionOrder\ReorderStage;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Shuffles the tests of a test suite using a random number generator of its
 * own, so that the order of the tests does not depend on the state of the
 * random number generator that is global to the PHP process, which is seeded
 * for, and used by, the code under test.
 *
 * The Mt19937 engine is used because it shuffles exactly like shuffle() does
 * after mt_srand() was called with the same seed, which is how tests were
 * shuffled before: a seed results in the same order as it did then.
 *
 * All test suites of a test run are shuffled by the same object, one after
 * the other, so the order of the tests of a test suite depends on the test
 * suites that were shuffled before it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Randomize implements ReorderStage
{
    private Randomizer $randomizer;

    public function __construct(int $seed)
    {
        $this->randomizer = new Randomizer(new Mt19937($seed));
    }

    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    public function apply(array $tests, Context $context): array
    {
        return $this->randomizer->shuffleArray($tests);
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return 'random';
    }
}
