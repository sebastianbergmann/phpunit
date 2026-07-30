<?php

declare(strict_types=1);

final class SomeClass
{
    private string $name = 'not empty class';

    public function getName(): string
    {
        return $this->name;
    }
}
