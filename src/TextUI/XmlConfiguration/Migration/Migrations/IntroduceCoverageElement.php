<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

class IntroduceCoverageElement implements Migration
{
    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var DOMElement
     */
    private $coverage;

    public function migrate(DOMDocument $document): void
    {
        $this->document = $document;
        $this->coverage = $document->createElement('coverage');

        $this->document->documentElement->insertBefore(
            $this->coverage,
            $this->document->documentElement->firstChild
        );
    }

}
