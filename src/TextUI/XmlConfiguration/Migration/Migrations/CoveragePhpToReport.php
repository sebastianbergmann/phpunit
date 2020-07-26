<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMElement;

class CoveragePhpToReport extends LogToReportMigration {

    protected function forType(): string {
        return 'coverage-php';
    }

    protected function toReportFormat(DOMElement $logNode): DOMElement {
        $php = $logNode->ownerDocument->createElement('php');
        $php->setAttribute('outputFile', $logNode->getAttribute('target'));

        return $php;
    }

}
