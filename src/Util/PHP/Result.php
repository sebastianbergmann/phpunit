<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Result
{
    private string $stdout;
    private string $stderr;
    private int $exitCode;

    public function __construct(string $stdout, string $stderr, int $exitCode)
    {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->exitCode = $exitCode;
    }

    public function stdout(): string
    {
        return $this->stdout;
    }

    public function stderr(): string
    {
        return $this->stderr;
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }
}
