<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use function in_array;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExcludeGroupFilterIterator extends GroupFilterIterator
{
    /**
     * @psalm-param non-empty-string $id
     * @psalm-param list<non-empty-string> $groupTests
     */
    protected function doAccept(string $id, array $groupTests): bool
    {
        return !in_array($id, $groupTests, true);
    }
}
