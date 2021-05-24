<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Runtime;

use function array_merge;
use function asort;
use function extension_loaded;
use function get_loaded_extensions;
use ArrayIterator;
use IteratorAggregate;

/**
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Extensions implements IteratorAggregate
{
    public function loaded(string $name): bool
    {
        return extension_loaded($name);
    }

    /**
     * @return ArrayIterator<int, string>
     */
    public function getIterator(): ArrayIterator
    {
        $all = array_merge(
            get_loaded_extensions(true),
            get_loaded_extensions(false)
        );

        asort($all);

        return new ArrayIterator($all);
    }
}
