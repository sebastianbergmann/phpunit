<?php


namespace PHPUnit\Framework\MockObject;


class SimpleType extends Type
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $allowsNull;

    public function __construct(string $name, bool $nullable)
    {
        $this->name = $this->normalize($name);
        $this->allowsNull = $nullable;
    }

    public function isAssignable(Type $other): bool
    {
        if ($this->allowsNull && isNull($other)) {
            return true;
        }
        if ($other instanceof self) {
            return $this->name === $other->name;
        }
        return false;
    }

    public function getReturnTypeDeclaration(): string
    {
        return ': ' . ($this->allowsNull ? '?' : '') . $this->name;
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower($name);
        switch ($name) {
            case 'boolean':
                return 'bool';
            case 'real':
            case 'double':
                return 'float';
            case 'integer':
                return 'int';
            case '[]':
                return 'array';
            default:
                return $name;
        }
    }
}
