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

use function end;
use function implode;
use function preg_match;
use function sprintf;
use function str_replace;
use Exception;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Util\RegularExpression;
use RecursiveFilterIterator;
use RecursiveIterator;
use PHPUnit\Framework\DataProviderTestSuite;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class LineFilterIterator extends RecursiveFilterIterator
{
    private int $line;

    /**
     * @throws Exception
     */
    public function __construct(RecursiveIterator $iterator, int $line)
    {
        parent::__construct($iterator);

        $this->line = $line;
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        if ($test instanceof TestCase) {
            return $this->line === $test->line();
        }

        return false;
    }
}
