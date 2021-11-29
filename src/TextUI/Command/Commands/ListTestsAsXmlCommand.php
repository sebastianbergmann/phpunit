<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use function file_put_contents;
use function sprintf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use RecursiveIteratorIterator;
use XMLWriter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ListTestsAsXmlCommand implements Command
{
    private string $filename;
    private TestSuite $suite;

    public function __construct(string $filename, TestSuite $suite)
    {
        $this->filename = $filename;
        $this->suite    = $suite;
    }

    public function execute(): Result
    {
        $writer = new XMLWriter;

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument();
        $writer->startElement('tests');

        $currentTestCase = null;

        foreach (new RecursiveIteratorIterator($this->suite->getIterator()) as $test) {
            if ($test instanceof TestCase) {
                if ($test::class !== $currentTestCase) {
                    if ($currentTestCase !== null) {
                        $writer->endElement();
                    }

                    $writer->startElement('testCaseClass');
                    $writer->writeAttribute('name', $test::class);

                    $currentTestCase = $test::class;
                }

                $writer->startElement('testCaseMethod');
                $writer->writeAttribute('name', $test->getName(false));
                $writer->writeAttribute('groups', implode(',', $test->groups()));

                if (!empty($test->getDataSetAsString())) {
                    $writer->writeAttribute(
                        'dataSet',
                        str_replace(
                            ' with data set ',
                            '',
                            $test->getDataSetAsString()
                        )
                    );
                }

                $writer->endElement();
            } elseif ($test instanceof PhptTestCase) {
                if ($currentTestCase !== null) {
                    $writer->endElement();

                    $currentTestCase = null;
                }

                $writer->startElement('phptFile');
                $writer->writeAttribute('path', $test->getName());
                $writer->endElement();
            }
        }

        if ($currentTestCase !== null) {
            $writer->endElement();
        }

        $writer->endElement();

        file_put_contents($this->filename, $writer->outputMemory());

        $buffer = sprintf(
            'Wrote list of tests that would have been run to %s' . PHP_EOL,
            $this->filename
        );

        return Result::from($buffer);
    }
}
