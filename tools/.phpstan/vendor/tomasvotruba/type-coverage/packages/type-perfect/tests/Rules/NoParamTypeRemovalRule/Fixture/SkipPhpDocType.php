<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Source\PhpDocType;

final class SkipPhpDocType extends PhpDocType
{
    /**
     * @param string|null $node
     */
    public function justPhpDocType($node = null)
    {
    }
}
