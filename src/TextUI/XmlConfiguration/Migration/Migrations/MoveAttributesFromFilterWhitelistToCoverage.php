<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

class MoveAttributesFromFilterWhitelistToCoverage implements Migration {
    public function migrate(DOMDocument $document): void {
        $whitelist = $document->getElementsByTagName('whitelist')->item(0);
        if (!$whitelist) {
            return;
        }

        /** @var ?DOMElement $coverage */
        $coverage = $document->getElementsByTagName('coverage')->item(0);
        if (!$coverage instanceof DOMElement) {
            throw new MigrationException('Unexpected state - No coverage element');
        }

        $map = [
            'addUncoveredFilesFromWhitelist' => 'includeUncoveredFiles',
            'processUncoveredFilesFromWhitelist' => 'processUncoveredFiles'
        ];

        foreach($map as $old => $new) {
            if (!$whitelist->hasAttribute($old)) {
                continue;
            }

            $coverage->setAttribute($new, $whitelist->getAttribute($old));
            $whitelist->removeAttribute($old);
        }
    }

}
