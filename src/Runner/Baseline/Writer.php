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

use function array_shift;
use function assert;
use function count;
use function dirname;
use function explode;
use function file_put_contents;
use function implode;
use function min;
use function range;
use function str_repeat;
use function str_replace;
use function str_starts_with;
use XMLWriter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Writer
{
    /**
     * @psalm-param non-empty-string $baselineFile
     */
    public function write(string $baselineFile, Baseline $baseline): void
    {
        $baselineDirectory = dirname($baselineFile);

        $writer = new XMLWriter;

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument();

        $writer->startElement('files');
        $writer->writeAttribute('version', (string) Baseline::VERSION);

        foreach ($baseline->groupedByFileAndLine() as $file => $lines) {
            assert(!empty($file));

            $writer->startElement('file');
            $writer->writeAttribute('path', $this->relativePathFromBaseline($baselineDirectory, $file));

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

        file_put_contents($baselineFile, $writer->outputMemory());
    }

    /**
     * @psalm-param non-empty-string $baselineDirectory
     * @psalm-param non-empty-string $file
     *
     * @psalm-return non-empty-string
     */
    private function relativePathFromBaseline(string $baselineDirectory, string $file): string
    {
        if (str_starts_with($file, $baselineDirectory . DIRECTORY_SEPARATOR)) {
            $result = str_replace($baselineDirectory . DIRECTORY_SEPARATOR, '', $file);

            assert(!empty($result));

            return $result;
        }

        $from   = explode(DIRECTORY_SEPARATOR, $baselineDirectory);
        $to     = explode(DIRECTORY_SEPARATOR, $file);
        $common = 0;

        foreach (range(1, min(count($from), count($to))) as $i) {
            if ($from[0] === $to[0]) {
                array_shift($from);
                array_shift($to);

                $common++;
            }
        }

        assert($common > 0);

        $result = str_repeat('..' . DIRECTORY_SEPARATOR, count($from)) . implode(DIRECTORY_SEPARATOR, $to);

        assert(!empty($result));

        return $result;
    }
}
