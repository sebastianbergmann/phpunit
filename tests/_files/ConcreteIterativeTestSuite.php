<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Framework\IterativeTestSuite;
use PHPUnit\Framework\Test;

final class ConcreteIterativeTestSuite extends IterativeTestSuite
{
    public function runTestCollectingEvents(Test $test): EventCollection
    {
        return $this->runCollectingEvents($test);
    }

    /**
     * @param list<Test> $tests
     */
    protected function execute(array $tests, Event\Emitter $emitter): void
    {
    }
}
