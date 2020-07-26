<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMElement;

class CoverageTextToReport extends LogToReportMigration {

    protected function forType(): string {
        return 'coverage-text';
    }

    protected function toReportFormat(DOMElement $logNode): DOMElement {
        $text = $logNode->ownerDocument->createElement('text');
        $text->setAttribute('outputFile', $logNode->getAttribute('target'));

        $this->migrateAttributes($logNode, $text, ['showUncoveredFiles', 'showOnlySummary']);

        return $text;
    }

}
