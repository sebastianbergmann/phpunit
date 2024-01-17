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
use function ksort;
use function sprintf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\TextUI\Configuration\Registry;
use RecursiveIteratorIterator;
use XMLWriter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListTestsAsXmlCommand implements Command
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
        $buffer = $this->warnAboutConflictingOptions();
        $writer = new XMLWriter;

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument();

        $writer->startElement('testSuite');
        $writer->writeAttribute('xmlns', 'https://xml.phpunit.de/testSuite');

        $writer->startElement('tests');

        $currentTestClass = null;
        $groups           = [];

        foreach (new RecursiveIteratorIterator($this->suite) as $test) {
            if ($test instanceof TestCase) {
                foreach ($test->groups() as $group) {
                    if (!isset($groups[$group])) {
                        $groups[$group] = [];
                    }

                    $groups[$group][] = $test->valueObjectForEvents()->id();
                }

                if ($test::class !== $currentTestClass) {
                    if ($currentTestClass !== null) {
                        $writer->endElement();
                    }

                    $writer->startElement('testClass');
                    $writer->writeAttribute('name', $test::class);
                    $writer->writeAttribute('file', $test->valueObjectForEvents()->file());

                    $currentTestClass = $test::class;
                }

                $writer->startElement('testMethod');
                $writer->writeAttribute('id', $test->valueObjectForEvents()->id());
                $writer->writeAttribute('name', $test->valueObjectForEvents()->methodName());
                $writer->endElement();

                continue;
            }

            if ($test instanceof PhptTestCase) {
                if ($currentTestClass !== null) {
                    $writer->endElement();

                    $currentTestClass = null;
                }

                $writer->startElement('phpt');
                $writer->writeAttribute('file', $test->getName());
                $writer->endElement();
            }
        }

        if ($currentTestClass !== null) {
            $writer->endElement();
        }

        $writer->endElement();

        ksort($groups);

        $writer->startElement('groups');

        foreach ($groups as $groupName => $testIds) {
            $writer->startElement('group');
            $writer->writeAttribute('name', $groupName);

            foreach ($testIds as $testId) {
                $writer->startElement('test');
                $writer->writeAttribute('id', $testId);
                $writer->endElement();
            }

            $writer->endElement();
        }

        $writer->endElement();
        $writer->endElement();

        file_put_contents($this->filename, $writer->outputMemory());

        $buffer .= sprintf(
            'Wrote list of tests that would have been run to %s' . PHP_EOL,
            $this->filename,
        );

        return Result::from($buffer);
    }

    private function warnAboutConflictingOptions(): string
    {
        $buffer = '';

        $configuration = Registry::get();

        if ($configuration->hasFilter()) {
            $buffer .= 'The --filter and --list-tests-xml options cannot be combined, --filter is ignored' . PHP_EOL;
        }

        if ($configuration->hasGroups()) {
            $buffer .= 'The --group and --list-tests-xml options cannot be combined, --group is ignored' . PHP_EOL;
        }

        if ($configuration->hasExcludeGroups()) {
            $buffer .= 'The --exclude-group and --list-tests-xml options cannot be combined, --exclude-group is ignored' . PHP_EOL;
        }

        if (!empty($buffer)) {
            $buffer .= PHP_EOL;
        }

        return $buffer;
    }
}
