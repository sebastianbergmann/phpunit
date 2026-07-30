<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\ReturnNullOverFalseRule\Fixture;

class Entity
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if (!$this->isPropertyVisible($name)) {
            return false;
        }

        return isset($this->$name);
    }

    private function isPropertyVisible(string $name): bool
    {
        if (mt_rand(1, 0)) {
            return true;
        }

        return false;
    }
}
