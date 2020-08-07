<?php declare(strict_types=1);

final class Greeter
{
    public function greet(string $name): string
    {
        $trimmed = trim($name);

        if ('' === $trimmed) {
            throw new \InvalidArgumentException('Name should not be a blank or empty string.');
        }

        return "Hello, {$name}!";
    }
}
