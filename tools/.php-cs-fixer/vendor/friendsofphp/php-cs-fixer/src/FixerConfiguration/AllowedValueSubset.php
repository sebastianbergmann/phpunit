<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\FixerConfiguration;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AllowedValueSubset
{
    /**
     * @var non-empty-list<string>
     */
    private array $allowedValues;

    /**
     * @param non-empty-list<string> $allowedValues
     */
    public function __construct(array $allowedValues)
    {
        sort($allowedValues, \SORT_FLAG_CASE | \SORT_STRING);
        $this->allowedValues = $allowedValues;
    }

    /**
     * Checks whether the given values are a subset of the allowed ones.
     *
     * @param mixed $values the value to validate
     */
    public function __invoke($values): bool
    {
        if (!\is_array($values)) {
            return false;
        }

        foreach ($values as $value) {
            if (!\in_array($value, $this->allowedValues, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getAllowedValues(): array
    {
        return $this->allowedValues;
    }
}
