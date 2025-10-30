<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function count;
use PHPUnit\Event;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @template T of TestCase|PhptTestCase
 */
abstract readonly class AbstractRepeatTestSuite implements Reorderable, Test
{
    /**
     * @var non-empty-list<T>
     */
    protected array $tests;

    /**
     * @param non-empty-list<T> $tests
     */
    public function __construct(array $tests)
    {
        $this->tests = $tests;
    }

    final public function count(): int
    {
        return count($this->tests);
    }

    final public function sortId(): string
    {
        return $this->tests[0]->sortId();
    }

    final public function provides(): array
    {
        return $this->tests[0]->provides();
    }

    final public function requires(): array
    {
        return $this->tests[0]->requires();
    }

    final public function valueObjectForEvents(): Event\Code\Phpt|Event\Code\TestMethod
    {
        return $this->tests[0]->valueObjectForEvents();
    }
}
