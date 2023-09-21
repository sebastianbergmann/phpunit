<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function array_map;
use function implode;
use function is_int;
use function preg_split;
use function sprintf;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\NoDataSetFromDataProviderException;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PlainTextRenderer
{
    /**
     * @psalm-param array<string, TestResultCollection> $tests
     */
    public function render(array $tests): string
    {
        $buffer = '';

        foreach ($tests as $prettifiedClassName => $_tests) {
            $buffer .= $prettifiedClassName . "\n";

            foreach ($_tests as $test) {
                $buffer .= $this->renderTestResult($test);
            }

            $buffer .= "\n";
        }

        return $buffer;
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    private function renderTestResult(TestResult $testResult): string
    {
        $method = $testResult->test();

        $status = $testResult->status();

        return sprintf(
            '%s%s',
            $this->renderTestResultHeader($method, $status),
            $this->renderTestResultBody($method, $status),
        );
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    private function renderTestResultHeader(TestMethod $method, TestStatus $status): string
    {
        $testStatus = $status->isSuccess() ? 'x' : ' ';

        $prettifiedMethodName = $method->testDox()->prettifiedMethodName();

        $testData = $method->testData();

        if ($testData->hasDataFromDataProvider()) {
            $dataSetNameOrInt = $testData->dataFromDataProvider()->dataSetName();

            $dataSetName = sprintf(
                ' with data set %s',
                is_int($dataSetNameOrInt) ? '#' . $dataSetNameOrInt : '"' . $dataSetNameOrInt . '"',
            );
        }

        return sprintf(
            ' [%s] %s%s%s',
            $testStatus,
            $prettifiedMethodName,
            $dataSetName ?? '',
            "\n",
        );
    }

    private function renderTestResultBody(TestMethod $method, TestStatus $status): string
    {
        if ($status->isSuccess()) {
            return '';
        }

        return sprintf(
            "%s\n%s\n%s\n%s\n",
            $this->prefixLines($this->prefixFor('start'), ''),
            $this->prefixLines($this->prefixFor('message'), $status->message()),
            $this->prefixLines($this->prefixFor('default'), sprintf("\n%s:%d", $method->file(), $method->line())),
            $this->prefixLines($this->prefixFor('last'), ''),
        );
    }

    private function prefixLines(string $prefix, string $message): string
    {
        return implode(
            "\n",
            array_map(
                static fn (string $line) => '      ' . $prefix . ($line ? ' ' . $line : ''),
                preg_split('/\r\n|\r|\n/', $message),
            ),
        );
    }

    /**
     * @psalm-param 'default'|'start'|'message'|'diff'|'trace'|'last' $type
     */
    private function prefixFor(string $type): string
    {
        return match ($type) {
            'default' => '│',
            'start'   => '┐',
            'message' => '├',
            'diff'    => '┊',
            'trace'   => '╵',
            'last'    => '┴',
        };
    }
}
