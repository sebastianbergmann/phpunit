<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestData;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class TestData
{
    private readonly SerializedValueCollection $serializedValues;
    private readonly string $stringRepresentation;

    protected function __construct(SerializedValueCollection $serializedValues, string $stringRepresentation)
    {
        $this->serializedValues     = $serializedValues;
        $this->stringRepresentation = $stringRepresentation;
    }

    public function serializedValues(): SerializedValueCollection
    {
        return $this->serializedValues;
    }

    public function asString(): string
    {
        return $this->stringRepresentation;
    }

    /**
     * @psalm-assert-if-true DataFromDataProvider $this
     */
    public function isFromDataProvider(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true DataFromTestDependency $this
     */
    public function isFromTestDependency(): bool
    {
        return false;
    }
}
