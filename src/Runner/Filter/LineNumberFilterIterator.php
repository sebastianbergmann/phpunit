<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use PHPUnit\Framework\TestSuite;
use RecursiveFilterIterator;
use RecursiveIterator;
use ReflectionMethod;

class LineNumberFilterIterator extends RecursiveFilterIterator
{
    /**
     * @var int
     */
    protected $lineNumber;

    /**
     * @throws \Exception
     */
    public function __construct(RecursiveIterator $iterator, int $lineNumber)
    {
        parent::__construct($iterator);

        $this->lineNumber = $lineNumber;
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        $method      = new ReflectionMethod($test, $test->getName(false));
        $startLine   = $method->getStartLine();
        $endLine     = $method->getEndLine();

        return $startLine <= $this->lineNumber && $endLine >= $this->lineNumber;
    }
}
