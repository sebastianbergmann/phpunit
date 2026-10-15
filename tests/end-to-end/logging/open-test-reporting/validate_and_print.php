<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use const PHP_EOL;
use function array_key_last;
use function array_search;
use function array_splice;
use function file_get_contents;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function printf;
use function sprintf;
use function trim;
use DOMDocument;
use DOMElement;

function validate_and_print(string $logfile): void
{
    libxml_use_internal_errors(true);

    $document = new DOMDocument;
    $document->load($logfile);

    if (!$document->schemaValidate(__DIR__ . '/../../../../src/Logging/OpenTestReporting/schema/otr.xsd')) {
        print 'Generated XML document does not validate against Open Test Reporting schemas:' . PHP_EOL . PHP_EOL;

        foreach (libxml_get_errors() as $error) {
            printf(
                '- Line %d: %s' . PHP_EOL,
                $error->line,
                trim($error->message),
            );
        }

        print PHP_EOL;
    }

    libxml_clear_errors();

    $violations = violations_of_event_invariants($document);

    if ($violations !== []) {
        print 'Generated XML document has inconsistent Open Test Reporting events:' . PHP_EOL . PHP_EOL;

        foreach ($violations as $violation) {
            print '- ' . $violation . PHP_EOL;
        }

        print PHP_EOL;
    }

    print file_get_contents($logfile);
}

/**
 * The schemas cannot express that the events describe a tree of nodes with
 * unique ids in which each node is started once, nested in the innermost node
 * that is not finished yet, reported on only between its start and its end,
 * and finished once, after the nodes nested in it.
 *
 * @return list<string>
 */
function violations_of_event_invariants(DOMDocument $document): array
{
    $events = $document->documentElement;

    if ($events === null) {
        return [];
    }

    $violations = [];
    $names      = [];
    $open       = [];

    foreach ($events->childNodes as $event) {
        if (!$event instanceof DOMElement || $event->namespaceURI !== $events->namespaceURI) {
            continue;
        }

        $id = $event->getAttribute('id');

        if ($id === '') {
            $violations[] = sprintf(
                'Line %d: <%s> has an empty id',
                $event->getLineNo(),
                $event->tagName,
            );

            continue;
        }

        if ($event->localName === 'started') {
            if (isset($names[$id])) {
                $violations[] = sprintf(
                    'Line %d: <%s id="%s"> reuses the id of "%s"',
                    $event->getLineNo(),
                    $event->tagName,
                    $id,
                    $names[$id],
                );

                continue;
            }

            if ($open === []) {
                if ($event->hasAttribute('parentId')) {
                    $violations[] = sprintf(
                        'Line %d: <%s id="%s"> has parentId="%s" although no node is unfinished',
                        $event->getLineNo(),
                        $event->tagName,
                        $id,
                        $event->getAttribute('parentId'),
                    );
                }
            } elseif ($event->getAttribute('parentId') !== $open[array_key_last($open)]) {
                $violations[] = sprintf(
                    'Line %d: <%s id="%s"> must have parentId="%s", the id of the innermost unfinished node "%s"',
                    $event->getLineNo(),
                    $event->tagName,
                    $id,
                    $open[array_key_last($open)],
                    $names[$open[array_key_last($open)]],
                );
            }

            $names[$id] = $event->getAttribute('name');
            $open[]     = $id;

            continue;
        }

        if ($event->localName !== 'reported' && $event->localName !== 'finished') {
            continue;
        }

        if (!isset($names[$id])) {
            $violations[] = sprintf(
                'Line %d: <%s id="%s"> refers to a node that was not started',
                $event->getLineNo(),
                $event->tagName,
                $id,
            );

            continue;
        }

        $position = array_search($id, $open, true);

        if ($position === false) {
            $violations[] = sprintf(
                'Line %d: <%s id="%s"> refers to "%s", which is already finished',
                $event->getLineNo(),
                $event->tagName,
                $id,
                $names[$id],
            );

            continue;
        }

        if ($event->localName === 'reported') {
            continue;
        }

        if ($position !== array_key_last($open)) {
            $violations[] = sprintf(
                'Line %d: <%s id="%s"> finishes "%s" before "%s" that is nested in it',
                $event->getLineNo(),
                $event->tagName,
                $id,
                $names[$id],
                $names[$open[array_key_last($open)]],
            );
        }

        array_splice($open, $position, 1);
    }

    foreach ($open as $id) {
        $violations[] = sprintf(
            'Node "%s" with id="%s" is never finished',
            $names[$id],
            $id,
        );
    }

    return $violations;
}
