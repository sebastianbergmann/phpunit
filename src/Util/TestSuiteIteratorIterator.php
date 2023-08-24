<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use IteratorAggregate;
use PHPUnit\Framework\TestSuite;
use RecursiveIterator;
use RecursiveIteratorIterator;

/**
 * @template-extends RecursiveIteratorIterator<TestSuite>
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class TestSuiteIteratorIterator extends RecursiveIteratorIterator
{
    public function callGetChildren(): ?RecursiveIterator
    {
        $current = $this->current();

        if (!$current instanceof IteratorAggregate) {
            return null;
        }

        $iterator = $current->getIterator();

        if (!$iterator instanceof RecursiveIterator) {
            return null;
        }

        return $iterator;
    }
}
