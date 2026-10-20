<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console\Report\FixReport;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Preg;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * @author Boris Gorbylev <ekho@ekho.name>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class JunitReporter implements ReporterInterface
{
    public function getFormat(): string
    {
        return 'junit';
    }

    public function generate(ReportSummary $reportSummary): string
    {
        if (!\extension_loaded('dom')) {
            throw new \RuntimeException('Cannot generate report! `ext-dom` is not available!');
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $testsuites = $dom->appendChild($dom->createElement('testsuites'));

        $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));
        \assert($testsuite instanceof \DOMElement);

        $testsuite->setAttribute('name', 'PHP CS Fixer');

        $properties = $dom->createElement('properties');
        $property = $dom->createElement('property');
        $property->setAttribute('name', 'about');
        $property->setAttribute('value', Application::getAbout());
        $properties->appendChild($property);
        $testsuite->appendChild($properties);

        if (\count($reportSummary->getChanged()) > 0) {
            $this->createFailedTestCases($dom, $testsuite, $reportSummary);
        } else {
            $this->createSuccessTestCase($dom, $testsuite);
        }

        if ($reportSummary->getTime() > 0) {
            $testsuite->setAttribute(
                'time',
                \sprintf(
                    '%.3f',
                    $reportSummary->getTime() / 1_000,
                ),
            );
        }

        $dom->formatOutput = true;

        $result = $dom->saveXML();
        if (false === $result) {
            throw new \RuntimeException('Failed to generate XML output');
        }

        return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($result) : $result;
    }

    private function createSuccessTestCase(\DOMDocument $dom, \DOMElement $testsuite): void
    {
        $testcase = $dom->createElement('testcase');
        $testcase->setAttribute('name', 'All OK');
        $testcase->setAttribute('assertions', '1');

        $testsuite->appendChild($testcase);
        $testsuite->setAttribute('tests', '1');
        $testsuite->setAttribute('assertions', '1');
        $testsuite->setAttribute('failures', '0');
        $testsuite->setAttribute('errors', '0');
    }

    private function createFailedTestCases(\DOMDocument $dom, \DOMElement $testsuite, ReportSummary $reportSummary): void
    {
        $assertionsCount = 0;
        foreach ($reportSummary->getChanged() as $file => $fixResult) {
            $testcase = $this->createFailedTestCase(
                $dom,
                $file,
                $fixResult,
                $reportSummary->shouldAddAppliedFixers(),
            );
            $testsuite->appendChild($testcase);
            $assertionsCount += (int) $testcase->getAttribute('assertions');
        }

        $testsuite->setAttribute('tests', (string) \count($reportSummary->getChanged()));
        $testsuite->setAttribute('assertions', (string) $assertionsCount);
        $testsuite->setAttribute('failures', (string) $assertionsCount);
        $testsuite->setAttribute('errors', '0');
    }

    /**
     * @param array{appliedFixers: list<string>, diff: string} $fixResult
     */
    private function createFailedTestCase(\DOMDocument $dom, string $file, array $fixResult, bool $shouldAddAppliedFixers): \DOMElement
    {
        $appliedFixersCount = \count($fixResult['appliedFixers']);

        $testName = str_replace('.', '_DOT_', Preg::replace('@\.'.pathinfo($file, \PATHINFO_EXTENSION).'$@', '', $file));

        $testcase = $dom->createElement('testcase');
        $testcase->setAttribute('name', $testName);
        $testcase->setAttribute('file', $file);
        $testcase->setAttribute('assertions', (string) $appliedFixersCount);

        $failure = $dom->createElement('failure');
        $failure->setAttribute('type', 'code_style');
        $testcase->appendChild($failure);

        if ($shouldAddAppliedFixers) {
            $failureContent = "applied fixers:\n---------------\n";

            foreach ($fixResult['appliedFixers'] as $appliedFixer) {
                $failureContent .= "* {$appliedFixer}\n";
            }
        } else {
            $failureContent = "Wrong code style\n";
        }

        if ('' !== $fixResult['diff']) {
            $failureContent .= "\nDiff:\n---------------\n\n".$fixResult['diff'];
        }

        $failure->appendChild($dom->createCDATASection(trim($failureContent)));

        return $testcase;
    }
}
