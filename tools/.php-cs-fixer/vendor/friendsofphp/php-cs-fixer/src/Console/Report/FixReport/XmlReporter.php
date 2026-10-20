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
final class XmlReporter implements ReporterInterface
{
    public function getFormat(): string
    {
        return 'xml';
    }

    public function generate(ReportSummary $reportSummary): string
    {
        if (!\extension_loaded('dom')) {
            throw new \RuntimeException('Cannot generate report! `ext-dom` is not available!');
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        // new nodes should be added to this or existing children
        $root = $dom->createElement('report');
        $dom->appendChild($root);

        $root->appendChild($this->createAboutElement($dom, Application::getAbout()));

        $filesXML = $dom->createElement('files');
        $root->appendChild($filesXML);

        $i = 1;
        foreach ($reportSummary->getChanged() as $file => $fixResult) {
            $fileXML = $dom->createElement('file');
            $fileXML->setAttribute('id', (string) $i++);
            $fileXML->setAttribute('name', $file);
            $filesXML->appendChild($fileXML);

            if ($reportSummary->shouldAddAppliedFixers()) {
                $fileXML->appendChild(
                    $this->createAppliedFixersElement($dom, $fixResult['appliedFixers']),
                );
            }

            if ('' !== $fixResult['diff']) {
                $fileXML->appendChild($this->createDiffElement($dom, $fixResult['diff']));
            }
        }

        if (0 !== $reportSummary->getTime()) {
            $root->appendChild($this->createTimeElement($reportSummary->getTime(), $dom));
        }

        if (0 !== $reportSummary->getMemory()) {
            $root->appendChild($this->createMemoryElement($reportSummary->getMemory(), $dom));
        }

        $dom->formatOutput = true;

        $result = $dom->saveXML();
        if (false === $result) {
            throw new \RuntimeException('Failed to generate XML output');
        }

        return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($result) : $result;
    }

    /**
     * @param list<string> $appliedFixers
     */
    private function createAppliedFixersElement(\DOMDocument $dom, array $appliedFixers): \DOMElement
    {
        $appliedFixersXML = $dom->createElement('applied_fixers');

        foreach ($appliedFixers as $appliedFixer) {
            $appliedFixerXML = $dom->createElement('applied_fixer');
            $appliedFixerXML->setAttribute('name', $appliedFixer);
            $appliedFixersXML->appendChild($appliedFixerXML);
        }

        return $appliedFixersXML;
    }

    private function createDiffElement(\DOMDocument $dom, string $diff): \DOMElement
    {
        $diffXML = $dom->createElement('diff');
        $diffXML->appendChild($dom->createCDATASection($diff));

        return $diffXML;
    }

    private function createTimeElement(float $time, \DOMDocument $dom): \DOMElement
    {
        $time = round($time / 1_000, 3);

        $timeXML = $dom->createElement('time');
        $timeXML->setAttribute('unit', 's');
        $timeTotalXML = $dom->createElement('total');
        $timeTotalXML->setAttribute('value', (string) $time);
        $timeXML->appendChild($timeTotalXML);

        return $timeXML;
    }

    private function createMemoryElement(float $memory, \DOMDocument $dom): \DOMElement
    {
        $memory = round($memory / 1_024 / 1_024, 3);

        $memoryXML = $dom->createElement('memory');
        $memoryXML->setAttribute('value', (string) $memory);
        $memoryXML->setAttribute('unit', 'MB');

        return $memoryXML;
    }

    private function createAboutElement(\DOMDocument $dom, string $about): \DOMElement
    {
        $xml = $dom->createElement('about');
        $xml->setAttribute('value', $about);

        return $xml;
    }
}
