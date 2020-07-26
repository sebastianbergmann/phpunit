<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

class ConvertLogTypes implements Migration {

    public function migrate(DOMDocument $document): void {

        $logging = $document->getElementsByTagName('logging')->item(0);
        if (!$logging instanceof DOMElement) {
            return;
        }
        $types = [
            'junit' => 'junit',
            'teamcity' => 'teamcity',
            'testdox-html' => 'testdoxHtml',
            'testdox-text' => 'testdoxText',
            'testdox-xml' => 'testdoxXml',
            'plain' => 'text'
        ];

        $logNodes = [];
        foreach($logging->getElementsByTagName('log') as $logNode) {
            if (!isset($types[$logNode->getAttribute('type')])) {
                continue;
            }

            $logNodes[] = $logNode;
        }

        foreach($logNodes as $oldNode) {
            $newLogNode = $document->createElement($types[$oldNode->getAttribute('type')]);
            $newLogNode->setAttribute('outputFile', $oldNode->getAttribute('target'));

            $logging->replaceChild($newLogNode, $oldNode);
        }

    }

}
