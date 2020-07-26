<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMElement;

class CoverageCrap4jToReport extends LogToReportMigration {

    protected function forType(): string {
        return 'coverage-crap4j';
    }

    protected function toReportFormat(DOMElement $logNode): DOMElement {
        $crap4j = $logNode->ownerDocument->createElement('crap4j');
        $crap4j->setAttribute('outputFile', $logNode->getAttribute('target'));

        $this->migrateAttributes($logNode, $crap4j, ['threshold']);

        return $crap4j;
    }

}
