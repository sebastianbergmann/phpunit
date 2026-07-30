<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class SkipIndirectRemoval extends PhpFileLoader
{
    /**
     * @param string|null $type
     */
    public function load(mixed $resource, string $type = null): mixed
    {
        return null;
    }
}
