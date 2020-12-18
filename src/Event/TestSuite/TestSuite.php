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

final class TestSuite
{
    private string $name;

    private int $numberOfTests;

    public function __construct(string $name, int $numberOfTests)
    {
        $this->name          = $name;
        $this->numberOfTests = $numberOfTests;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function numberOfTests(): int
    {
        return $this->numberOfTests;
    }
}
