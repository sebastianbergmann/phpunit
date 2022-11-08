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
final class DataFromDataProvider extends TestData
{
    private readonly int|string $dataSetName;

    public static function from(int|string $dataSetName, string $data): self
    {
        return new self($dataSetName, $data);
    }

    protected function __construct(int|string $dataSetName, string $data)
    {
        $this->dataSetName = $dataSetName;

        parent::__construct($data);
    }

    public function dataSetName(): int|string
    {
        return $this->dataSetName;
    }

    /**
     * @psalm-assert-if-true DataFromDataProvider $this
     */
    public function isFromDataProvider(): bool
    {
        return true;
    }
}
