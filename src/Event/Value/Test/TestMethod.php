<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Metadata\MetadataCollection;

/**
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TestMethod extends Test
{
    /**
     * @psalm-var class-string
     */
    private string $className;

    private string $methodName;

    private int|string $dataSetName;

    private string $dataSet;

    private int $line;

    private MetadataCollection $metadata;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className, string $methodName, int|string $dataSetName, string $dataSet, string $file, int $line, MetadataCollection $metadata)
    {
        parent::__construct($file);

        $this->className   = $className;
        $this->methodName  = $methodName;
        $this->dataSetName = $dataSetName;
        $this->dataSet     = $dataSet;
        $this->line        = $line;
        $this->metadata    = $metadata;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function usesProvidedData(): bool
    {
        return $this->dataSetName !== '';
    }

    public function dataSetName(): int|string
    {
        return $this->dataSetName;
    }

    public function dataSetAsString(): string
    {
        return $this->dataSet;
    }

    public function line(): int
    {
        return $this->line;
    }

    public function metadata(): MetadataCollection
    {
        return $this->metadata;
    }

    /**
     * @psalm-assert-if-true TestMethod $this
     */
    public function isTestMethod(): bool
    {
        return true;
    }
}
