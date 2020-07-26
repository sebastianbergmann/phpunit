<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

class MoveWhitelistDirectoriesToCoverage implements Migration {

    public function migrate(DOMDocument $document): void {
        $whitelist = $document->getElementsByTagName('whitelist')->item(0);
        if ($whitelist === null) {
            return;
        }

        /** @var ?DOMElement $coverage */
        $coverage = $document->getElementsByTagName('coverage')->item(0);
        if (!$coverage instanceof DOMElement) {
            throw new MigrationException('Unexpected state - No coverage element');
        }

        $include = $document->createElement('include');
        $coverage->appendChild($include);

        foreach($whitelist->childNodes as $child) {
            if (!$child instanceof DOMElement || $child->nodeName !== 'directory') {
                continue;
            }

            $include->appendChild($child);
        }

    }

}
