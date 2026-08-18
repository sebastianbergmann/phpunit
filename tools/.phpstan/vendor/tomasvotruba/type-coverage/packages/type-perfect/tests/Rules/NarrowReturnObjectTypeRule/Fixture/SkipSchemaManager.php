<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use Countable;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

final class SkipSchemaManager
{
    public function create(AbstractSchemaManager $schemaManager): Countable
    {
        return $schemaManager;
    }
}
