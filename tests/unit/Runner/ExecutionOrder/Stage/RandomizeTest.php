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

use function mt_rand;
use function mt_srand;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ExecutionOrder\Context;
use PHPUnit\Runner\TestRunHistory\NullTestRunHistory;

#[CoversClass(Randomize::class)]
#[UsesClass(Context::class)]
#[Small]
final class RandomizeTest extends TestCase
{
    protected function tearDown(): void
    {
        mt_srand();
    }

    public function testIsNamedRandom(): void
    {
        $this->assertSame('random', new Randomize(1234)->name());
    }

    /**
     * The expected order is the one that shuffle() produces after
     * mt_srand(1234) was called.
     */
    public function testShufflesTestsInTheOrderThatShuffleProducesForTheSameSeed(): void
    {
        $tests = $this->tests(5);

        $this->assertSame(
            [$tests[2], $tests[1], $tests[4], $tests[3], $tests[0]],
            new Randomize(1234)->apply($tests, $this->context()),
        );
    }

    /**
     * The expected order of the second list is the one that the second call of
     * shuffle() produces after mt_srand(1234) was called.
     */
    public function testShufflesOneListOfTestsAfterTheOtherUsingTheSameSequenceOfRandomNumbers(): void
    {
        $stage = new Randomize(1234);
        $tests = $this->tests(3);

        $stage->apply($this->tests(5), $this->context());

        $this->assertSame(
            [$tests[1], $tests[0], $tests[2]],
            $stage->apply($tests, $this->context()),
        );
    }

    public function testDifferentSeedsResultInDifferentOrders(): void
    {
        $tests = $this->tests(5);

        $this->assertNotSame(
            new Randomize(1234)->apply($tests, $this->context()),
            new Randomize(4321)->apply($tests, $this->context()),
        );
    }

    public function testDoesNotUseTheRandomNumberGeneratorThatIsGlobalToTheProcess(): void
    {
        mt_srand(1234);

        $expected = mt_rand();

        mt_srand(1234);

        new Randomize(1234)->apply($this->tests(5), $this->context());

        $this->assertSame($expected, mt_rand());
    }

    /**
     * @param positive-int $numberOfTests
     *
     * @return non-empty-list<Test>
     */
    private function tests(int $numberOfTests): array
    {
        $tests = [];

        for ($i = 0; $i < $numberOfTests; $i++) {
            $tests[] = $this->createStub(Test::class);
        }

        return $tests;
    }

    private function context(): Context
    {
        return new Context(
            TestSuite::empty('test', $this->createStub(Emitter::class)),
            new NullTestRunHistory,
        );
    }
}
