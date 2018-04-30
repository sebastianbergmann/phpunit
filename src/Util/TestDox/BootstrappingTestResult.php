<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\TestDox;

use Throwable;

final class BootstrappingTestResult implements TestResult
{
    /**
     * @var bool
     */
    private $successful = true;

    /**
     * @return null|Throwable
     */
    private $throwable;

    /**
     * @var string
     */
    private $symbol;

    public function isTestSuccessful(): bool
    {
        return $this->successful;
    }

    public function fail(
        string $symbol,
        Throwable $throwable,
        bool $verbose = false
    ): void {
        $this->successful = false;
        $this->symbol     = $symbol;
        $this->throwable  = $throwable;
    }

    public function setRuntime(float $runtime): void
    {
    }

    public function toString(?TestResult $previousTestResult, $verbose = false): string
    {
        if (null === $this->throwable) {
            return '';
        }

        return \sprintf(
            " %s %s in %s:%d\n%s",
            $this->symbol,
            $this->throwable->getMessage(),
            $this->throwable->getFile(),
            $this->throwable->getLine(),
            $this->indentedTrace($this->throwable->getTraceAsString())
        );
    }

    private function indentedTrace(string $trace): string
    {
        return \sprintf(
            "   │\n%s\n",
            \implode(
                "\n",
                \array_map(
                    function (string $text) {
                        return \sprintf('   │ %s', $text);
                    },
                    \explode("\n", $trace)
                )
            )
        );
    }
}
