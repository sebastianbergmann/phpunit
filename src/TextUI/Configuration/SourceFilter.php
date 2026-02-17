<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use SebastianBergmann\FileFilter\Filter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SourceFilter
{
    private static ?self $instance = null;
    private readonly Filter $filter;

    public static function instance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self(
            (new FileFilterMapper)->map(
                Registry::get()->source(),
            ),
        );

        return self::$instance;
    }

    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param non-empty-string $path
     */
    public function includes(string $path): bool
    {
        return $this->filter->accepts($path);
    }
}
