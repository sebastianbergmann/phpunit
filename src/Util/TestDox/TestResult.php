<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

final class TestResult
{
    /**
     * @var callable
     */
    private $colorize;

    /**
     * @var string
     */
    private $testClass;

    /**
     * @var string
     */
    private $testMethod;

    /**
     * @var bool
     */
    private $testSuccesful;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string
     */
    private $additionalInformation;

    /**
     * @var bool
     */
    private $additionalInformationVerbose;

    /**
     * @var float
     */
    private $runtime;

    public function __construct(callable $colorize, string $testClass, string $testMethod)
    {
        $this->colorize              = $colorize;
        $this->testClass             = $testClass;
        $this->testMethod            = $testMethod;
        $this->testSuccesful         = true;
        $this->symbol                = ($this->colorize)('fg-green', '✔');
        $this->additionalInformation = '';
    }

    public function isTestSuccessful(): bool
    {
        return $this->testSuccesful;
    }

    public function fail(string $symbol, string $additionalInformation, bool $additionalInformationVerbose = false): void
    {
        $this->testSuccesful                = false;
        $this->symbol                       = $symbol;
        $this->additionalInformation        = $additionalInformation;
        $this->additionalInformationVerbose = $additionalInformationVerbose;
    }

    public function setRuntime(float $runtime): void
    {
        $this->runtime = $runtime;
    }

    public function toString(?self $previousTestResult, $verbose = false): string
    {
        return \sprintf(
            "%s%s %s %s%s\n%s",
            $previousTestResult && $previousTestResult->additionalInformationPrintable($verbose) ? PHP_EOL : '',
            $this->getClassNameHeader($previousTestResult ? $previousTestResult->testClass : null),
            $this->symbol,
            $this->testMethod,
            $verbose ? ' ' . $this->getFormattedRuntime() : '',
            $this->getFormattedAdditionalInformation($verbose)
        );
    }

    private function getClassNameHeader(?string $previousTestClass): string
    {
        $className = '';

        if ($this->testClass !== $previousTestClass) {
            if (null !== $previousTestClass) {
                $className = PHP_EOL;
            }

            $className .= \sprintf("%s\n", $this->testClass);
        }

        return $className;
    }

    private function getFormattedRuntime(): string
    {
        if ($this->runtime > 5) {
            return ($this->colorize)('fg-red', \sprintf('[%.2f ms]', $this->runtime * 1000));
        }

        if ($this->runtime > 1) {
            return ($this->colorize)('fg-yellow', \sprintf('[%.2f ms]', $this->runtime * 1000));
        }

        return \sprintf('[%.2f ms]', $this->runtime * 1000);
    }

    private function getFormattedAdditionalInformation($verbose): string
    {
        if (!$this->additionalInformationPrintable($verbose)) {
            return '';
        }

        return \sprintf(
            "   │\n%s\n",
            \implode(
                PHP_EOL,
                \array_map(
                    function (string $text) {
                        return \sprintf('   │ %s', $text);
                    },
                    \explode(PHP_EOL, $this->additionalInformation)
                )
            )
        );
    }

    private function additionalInformationPrintable(bool $verbose): bool
    {
        if ($this->additionalInformation === '') {
            return false;
        }

        if ($this->additionalInformationVerbose && !$verbose) {
            return false;
        }

        return true;
    }
}
