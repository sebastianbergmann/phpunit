<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * Names a file or a directory a test depends on that executing the test does
 * not show: the data a data provider reads, for instance, or a fixture a test
 * loads.
 *
 * The path is relative to the file the attribute is written in, unless it is
 * absolute.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class UsesFixture
{
    /**
     * @var non-empty-string
     */
    private string $path;

    /**
     * @param non-empty-string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return non-empty-string
     */
    public function path(): string
    {
        return $this->path;
    }
}
