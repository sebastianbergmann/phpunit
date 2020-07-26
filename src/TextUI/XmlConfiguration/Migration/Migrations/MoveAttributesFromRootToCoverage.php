<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

class MoveAttributesFromRootToCoverage implements Migration {

    public function migrate(DOMDocument $document): void {
        $map = [
            'disableCodeCoverageIgnore' => 'disableCodeCoverageIgnore',
            'ignoreDeprecatedCodeUnitsFromCodeCoverage' => 'ignoreDeprecatedCodeUnits'
        ];

        $root = $document->documentElement;

        /** @var ?DOMElement $coverage */
        $coverage = $document->getElementsByTagName('coverage')->item(0);
        if (!$coverage instanceof DOMElement) {
            throw new MigrationException('Unexpected state - No coverage element');
        }

        foreach($map as $old => $new) {
            if (!$root->hasAttribute($old)) {
                continue;
            }

            $coverage->setAttribute($new, $root->getAttribute($old));
            $root->removeAttribute($old);
        }
    }

}
