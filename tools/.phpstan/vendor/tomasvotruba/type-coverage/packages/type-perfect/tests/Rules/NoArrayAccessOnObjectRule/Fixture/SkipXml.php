<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Fixture;

use SimpleXMLElement;

final class SkipXml
{
    public function run(SimpleXMLElement $values)
    {
        return $values['key'];
    }
}
