<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

final class SkipResource
{
    /**
     * @param resource $resource
     */
    public function map($resource, string $name): void
    {
    }

    public function run()
    {
        $resource = @fsockopen('100', 100);
        $this->map($resource, 'name');


        $resource = $this->getFileResource();
        $this->map($resource, 'surname');
    }

    /**
     * @return resource
     */
    private function getFileResource()
    {
        return fopen('file.txt', 'r');
    }
}
