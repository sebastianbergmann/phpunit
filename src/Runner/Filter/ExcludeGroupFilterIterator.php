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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExcludeGroupFilterIterator extends GroupFilterIterator
{
    protected function doAccept(string $hash): bool
    {
        return !\in_array($hash, $this->groupTests, true);
    }
}
