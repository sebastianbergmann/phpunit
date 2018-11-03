<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

class Issue3379TestListener implements TestListener
{
    use TestListenerDefaultImplementation;

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        if ($test instanceof TestCase) {
            print 'Skipped test ' . $test->getName() . ', status: ' . $test->getStatus() . \PHP_EOL;
        }
    }
}
