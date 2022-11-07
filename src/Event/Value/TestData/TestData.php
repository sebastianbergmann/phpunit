<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class TestData
{
    private readonly ?string $serializedData;
    private readonly string $dataAsString;

    protected function __construct(?string $serializedData, string $dataAsString)
    {
        $this->serializedData = $serializedData;
        $this->dataAsString   = $dataAsString;
    }

    /**
     * @psalm-assert-if-true !null $this->serializedData
     */
    public function hasSerializedData(): bool
    {
        return $this->serializedData !== null;
    }

    /**
     * @throws SerializedDataNotAvailableException
     */
    public function serializedData(): string
    {
        if ($this->serializedData === null) {
            throw new SerializedDataNotAvailableException;
        }

        return $this->serializedData;
    }

    public function asString(): string
    {
        return $this->dataAsString;
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
