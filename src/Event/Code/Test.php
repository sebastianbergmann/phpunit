<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

final class Test
{
    /**
     * @psalm-var class-string
     */
    private string $className;

    private string $testName;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className, string $testName)
    {
        $this->className = $className;
        $this->testName  = $testName;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function testName(): string
    {
        return $this->testName;
    }
}
