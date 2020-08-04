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

final class UpdateSchemaLocationTo93 implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $document->documentElement->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:noNamespaceSchemaLocation',
            'https://schema.phpunit.de/9.3/phpunit.xsd'
        );
    }
}
