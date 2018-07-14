<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

/**
 * Class ArrayStructure
 */
class ArrayStructure extends Constraint
{
    /**
     * @var array
     */
    private $expectedStructure;

    /**
     * @var bool
     */
    private $strict;

    /**
     * @var string
     */
    private $additionalFailureDescription = '';

    /**
     * @param $expectedStructure
     * @param $strict
     */
    public function __construct($expectedStructure, $strict)
    {
        parent::__construct();

        $this->strict            = $strict;
        $this->expectedStructure = $expectedStructure;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        return 'matches the given structure.';
    }

    protected function matches($other): bool
    {
        if (\is_array($other)) {
            $result = $this->checkArrayStructureRecursive($this->expectedStructure, $other, '', $this->strict);

            if (!($result)) {
                return true;
            }
            $this->additionalFailureDescription = $result;
        }

        return false;
    }

    protected function additionalFailureDescription($other): string
    {
        return $this->additionalFailureDescription;
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }

    /**
     * Recursively checks the structure of the given array against the structure provided in $expectedStructure
     */
    private function checkArrayStructureRecursive(
        array $expected,
        array $actual,
        string $path,
        bool $strict = false
    ): string {
        foreach ($expected as $key => $value) {
            if (\is_array($value)) {
                if (!\array_key_exists($key, $actual)) {
                    return $path . $key . ' not available.';
                }

                if (!\is_array($actual[$key])) {
                    return $path . $key . ' is not an array.';
                }
                $nextLevelResult = self::checkArrayStructureRecursive($expected[$key], $actual[$key], $path . $key . ' => ');

                if (!empty($nextLevelResult)) {
                    return $nextLevelResult;
                }
            } else {
                if (!\array_key_exists($value, $actual)) {
                    return $path . $value . ' not available.';
                }

                if (empty($actual[$value]) && \gettype($actual[$value]) != 'boolean') {
                    return $path . $value . ' is empty.';
                }
            }
        }

        if ($strict && \count($expected) != \count($actual)) {
            return 'Both arrays do not have the same length.';
        }

        return '';
    }
}
