<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;

class XmlTestListRenderer
{
    public function render(TestSuite $suite): string
    {
        $writer = new \XmlWriter;

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument();
        $writer->startElement('tests');

        $currentTestCase = null;

        foreach (new \RecursiveIteratorIterator($suite->getIterator()) as $test) {
            if ($test instanceof TestCase) {
                if (\get_class($test) !== $currentTestCase) {
                    if ($currentTestCase !== null) {
                        $writer->endElement();
                    }

                    $writer->startElement('testCaseClass');
                    $writer->writeAttribute('name', \get_class($test));

                    $currentTestCase = \get_class($test);
                }

                $writer->startElement('testCaseMethod');
                $writer->writeAttribute('name', $test->getName(false));
                $writer->writeAttribute('groups', \implode(',', $test->getGroups()));

                if (!empty($test->getDataSetAsString(false))) {
                    $writer->writeAttribute(
                        'dataSet',
                        \str_replace(
                            ' with data set ',
                            '',
                            $test->getDataSetAsString(false)
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
            } else {
                continue;
            }
        }

        if ($currentTestCase !== null) {
            $writer->endElement();
        }

        $writer->endElement();

        return $writer->outputMemory();
    }
}
