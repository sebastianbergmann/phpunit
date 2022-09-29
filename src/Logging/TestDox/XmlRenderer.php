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

use function assert;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Metadata\Covers;
use PHPUnit\Metadata\Uses;
use XMLWriter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class XmlRenderer
{
    /**
     * @psalm-param array<class-string, TestMethodCollection> $tests
     */
    public function render(array $tests): string
    {
        $writer = new XMLWriter;

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('tests');

        $prettifier = new NamePrettifier;
        $parser     = new InlineAnnotationParser;

        foreach ($tests as $className => $_tests) {
            foreach ($_tests as $test) {
                $methodName = $test->test()->methodName();

                $writer->startElement('test');

                $writer->writeAttribute('className', $className);
                $writer->writeAttribute('methodName', $methodName);
                $writer->writeAttribute('prettifiedClassName', $prettifier->prettifyTestClass($className));
                $writer->writeAttribute('prettifiedMethodName', $prettifier->prettifyTestMethod($methodName));
                $writer->writeAttribute('size', (new Groups)->size($className, $methodName)->asString());
                $writer->writeAttribute('time', (string) $test->duration()->asFloat());
                $writer->writeAttribute('status', $test->status()->asString());

                if ($test->status()->isError() || $test->status()->isFailure()) {
                    $writer->writeAttribute('exceptionMessage', $test->status()->message());
                }

                $annotations = $parser->parse($className, $methodName);

                if (isset($annotations['given'], $annotations['when'], $annotations['then'])) {
                    $writer->writeAttribute('given', $annotations['given']['value']);
                    $writer->writeAttribute('givenStartLine', (string) $annotations['given']['line']);
                    $writer->writeAttribute('when', $annotations['when']['value']);
                    $writer->writeAttribute('whenStartLine', (string) $annotations['when']['line']);
                    $writer->writeAttribute('then', $annotations['then']['value']);
                    $writer->writeAttribute('thenStartLine', (string) $annotations['then']['line']);
                }

                foreach ((new Groups)->groups($className, $methodName, false) as $group) {
                    $writer->startElement('group');
                    $writer->writeAttribute('name', $group);
                    $writer->endElement();
                }

                foreach ($test->test()->metadata()->isCovers() as $covers) {
                    assert($covers instanceof Covers);

                    $writer->startElement('covers');
                    $writer->writeAttribute('target', $covers->target());
                    $writer->endElement();
                }

                foreach ($test->test()->metadata()->isUses() as $uses) {
                    assert($uses instanceof Uses);

                    $writer->startElement('uses');
                    $writer->writeAttribute('target', $uses->target());
                    $writer->endElement();
                }

                foreach ($test->testDoubles() as $testDouble) {
                    $writer->startElement('testDouble');
                    $writer->writeAttribute('type', $testDouble);
                    $writer->endElement();
                }

                $writer->endElement();
            }
        }

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }
}
