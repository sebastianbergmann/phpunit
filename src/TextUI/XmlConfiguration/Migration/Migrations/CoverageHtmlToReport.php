<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMElement;

class CoverageHtmlToReport extends LogToReportMigration {

    protected function forType(): string {
        return 'coverage-html';
    }

    protected function toReportFormat(DOMElement $logNode): DOMElement {
        $html = $logNode->ownerDocument->createElement('html');
        $html->setAttribute('outputDirectory', $logNode->getAttribute('target'));

        $this->migrateAttributes($logNode, $html, ['lowUpperBound', 'highLowerBound']);

        return $html;
    }

}
