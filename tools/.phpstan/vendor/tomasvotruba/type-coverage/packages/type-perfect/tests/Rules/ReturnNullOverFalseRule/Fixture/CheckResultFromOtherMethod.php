<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\ReturnNullOverFalseRule\Fixture;

class Loader
{
    /**
     * @return bool
     */
    public function exists(string $name)
    {
        if (!$this->loadFromDb($name)) {
            return false;
        }

        return true;
    }

    private function loadFromDb(string $name): ?array
    {
        if (mt_rand(1, 0)) {
            return null;
        }

        return ['test' => 'foo'];
    }
}
