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

class RemoveEmptyCoverageTag implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $node = $document->getElementsByTagName('coverage')->item(0);

        if (!$node instanceof DOMElement || $node->parentNode === null) {
            return;
        }

        if ($node->childElementCount !== 0) {
            return;
        }

        $node->parentNode->removeChild($node);
    }
}
