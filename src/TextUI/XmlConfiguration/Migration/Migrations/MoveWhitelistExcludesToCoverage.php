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

class MoveWhitelistExcludesToCoverage implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $whitelist = $document->getElementsByTagName('whitelist')->item(0);

        if ($whitelist === null) {
            return;
        }

        $exclude = $whitelist->getElementsByTagName('exclude')->item(0);

        if ($exclude === null) {
            return;
        }

        /** @var ?DOMElement $coverage */
        $coverage = $document->getElementsByTagName('coverage')->item(0);

        if (!$coverage instanceof DOMElement) {
            throw new MigrationException('Unexpected state - No coverage element');
        }

        $coverage->appendChild($exclude);
    }
}
