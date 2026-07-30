<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Fixture;

use Symfony\Component\DomCrawler\Link;

final class SkipUriElement
{
    public function run(Link $link)
    {
        return $link['href'];
    }
}
