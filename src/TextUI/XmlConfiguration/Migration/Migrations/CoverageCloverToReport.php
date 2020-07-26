<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMElement;

class CoverageCloverToReport extends LogToReportMigration {

    protected function forType(): string {
        return 'coverage-clover';
    }

    protected function toReportFormat(DOMElement $logNode): DOMElement {
        $clover = $logNode->ownerDocument->createElement('clover');
        $clover->setAttribute('outputFile', $logNode->getAttribute('target'));

        return $clover;
    }

}
