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

use DOMElement;

class CoverageCloverToReport extends LogToReportMigration
{
    protected function forType(): string
    {
        return 'coverage-clover';
    }

    protected function toReportFormat(DOMElement $logNode): DOMElement
    {
        $clover = $logNode->ownerDocument->createElement('clover');
        $clover->setAttribute('outputFile', $logNode->getAttribute('target'));

        return $clover;
    }
}
