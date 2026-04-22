<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class HookMethod
{
    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @var non-negative-int
     */
    private int $priority;

    /**
     * @param non-empty-string $methodName
     * @param non-negative-int $priority
     */
    public function __construct(string $methodName, int $priority)
    {
        $this->methodName = $methodName;
        $this->priority   = $priority;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return non-negative-int
     */
    public function priority(): int
    {
        return $this->priority;
    }
}
