<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use function file_put_contents;
use XMLWriter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Writer
{
    public function write(string $target, Baseline $baseline): void
    {
        $writer = new XMLWriter;

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument();
        $writer->startElement('files');

        foreach ($baseline->groupedByFileAndLine() as $file => $lines) {
            $writer->startElement('file');
            $writer->writeAttribute('path', $file);

            foreach ($lines as $line => $issues) {
                $writer->startElement('line');
                $writer->writeAttribute('number', (string) $line);
                $writer->writeAttribute('hash', $issues[0]->hash());

                foreach ($issues as $issue) {
                    $writer->startElement('issue');
                    $writer->writeCData($issue->description());
                    $writer->endElement();
                }

                $writer->endElement();
            }

            $writer->endElement();
        }

        $writer->endElement();

        file_put_contents($target, $writer->outputMemory());
    }
}
