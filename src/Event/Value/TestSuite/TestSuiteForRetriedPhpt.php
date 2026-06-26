<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Code\TestCollection;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteForRetriedPhpt extends TestSuite
{
    /**
     * @var positive-int
     */
    private int $maxAttempts;

    /**
     * @param non-empty-string $name
     * @param positive-int     $maxAttempts
     */
    public function __construct(string $name, int $size, TestCollection $tests, int $maxAttempts)
    {
        parent::__construct($name, $size, $tests);

        $this->maxAttempts = $maxAttempts;
    }

    /**
     * @return positive-int
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function isForRetriedPhpt(): true
    {
        return true;
    }
}
