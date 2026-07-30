<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Source;

class PhpDocType
{
    /**
     * @param string|null $value
     */
    public function justPhpDocType($node = null)
    {
    }
}
