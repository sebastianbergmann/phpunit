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

use function is_int;
use function sprintf;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Metadata\MetadataCollection;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestMethod extends Test
{
    /**
     * @var class-string
     */
    private string $className;

    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @var non-negative-int
     */
    private int $line;
    private TestDox $testDox;
    private MetadataCollection $metadata;
    private TestDataCollection $testData;

    /**
     * @var positive-int
     */
    private int $repeatAttemptNumber;

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     * @param non-empty-string $file
     * @param non-negative-int $line
     * @param positive-int     $repeatAttemptNumber
     */
    public function __construct(string $className, string $methodName, string $file, int $line, TestDox $testDox, MetadataCollection $metadata, TestDataCollection $testData, int $repeatAttemptNumber = 1)
    {
        parent::__construct($file);

        $this->className           = $className;
        $this->methodName          = $methodName;
        $this->line                = $line;
        $this->testDox             = $testDox;
        $this->metadata            = $metadata;
        $this->testData            = $testData;
        $this->repeatAttemptNumber = $repeatAttemptNumber;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return non-negative-int
     */
    public function line(): int
    {
        return $this->line;
    }

    public function testDox(): TestDox
    {
        return $this->testDox;
    }

    public function metadata(): MetadataCollection
    {
        return $this->metadata;
    }

    public function testData(): TestDataCollection
    {
        return $this->testData;
    }

    public function isTestMethod(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function id(): string
    {
        $buffer = $this->className . '::' . $this->methodName;

        if ($this->testData()->hasDataFromDataProvider()) {
            $buffer .= '#' . $this->testData->dataFromDataProvider()->dataSetName();
        }

        return $buffer;
    }

    /**
     * @return non-empty-string
     */
    public function nameWithClass(): string
    {
        return $this->className . '::' . $this->name();
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        if (!$this->testData->hasDataFromDataProvider()) {
            $name = $this->methodName;

            if ($this->repeatAttemptNumber !== 1) {
                $name .= " (repeat attempt #{$this->repeatAttemptNumber})";
            }

            return $name;
        }

        $dataSetName = $this->testData->dataFromDataProvider()->dataSetName();

        if (is_int($dataSetName)) {
            $dataSetName = sprintf(
                ' with data set #%d',
                $dataSetName,
            );
        } else {
            $dataSetName = sprintf(
                ' with data set "%s"',
                $dataSetName,
            );
        }

        $name = $this->methodName . $dataSetName;

        if ($this->repeatAttemptNumber !== 1) {
            $name .= " (repeat attempt #{$this->repeatAttemptNumber})";
        }

        return $name;
    }
}
