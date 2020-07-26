<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

class MoveWhitelistExcludesToCoverage implements Migration {

    public function migrate(DOMDocument $document): void {
        $whitelist = $document->getElementsByTagName('whitelist')->item(0);
        if ($whitelist === null) {
            return;
        }

        $exclude = $whitelist->getElementsByTagName('exclude')->item(0);
        if ($exclude === null) {
            return;
        }

        /** @var ?DOMElement $coverage */
        $coverage = $document->getElementsByTagName('coverage')->item(0);
        if (!$coverage instanceof DOMElement) {
            throw new MigrationException('Unexpected state - No coverage element');
        }

        $coverage->appendChild($exclude);
    }

}
