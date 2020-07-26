<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

class RemoveEmptyFilter implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $whitelist = $document->getElementsByTagName('whitelist')->item(0);

        if ($whitelist instanceof DOMElement) {
            $this->ensureEmpty($whitelist);
            $whitelist->parentNode->removeChild($whitelist);
        }

        $filter = $document->getElementsByTagName('filter')->item(0);

        if ($filter instanceof DOMElement) {
            $this->ensureEmpty($filter);
            $filter->parentNode->removeChild($filter);
        }
    }

    private function ensureEmpty(DOMElement $element): void
    {
        if ($element->attributes->length > 0) {
            throw new MigrationException(sprintf('%s element has unexpected attributes', $element->nodeName));
        }

        if ($element->getElementsByTagName('*')->length > 0) {
            throw new MigrationException(sprintf('%s element has unexpected children', $element->nodeName));
        }
    }
}
