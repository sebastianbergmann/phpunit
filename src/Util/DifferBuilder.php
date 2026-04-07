<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DifferBuilder
{
    /**
     * @param non-empty-string $header
     */
    public static function build(string $header = "--- Expected\n+++ Actual\n"): Differ
    {
        $contextLines = ConfigurationRegistry::get()->diffContext();

        return new Differ(
            new UnifiedDiffOutputBuilder(
                $header,
                false,
                $contextLines,
            ),
        );
    }

    public static function configureComparatorFactory(): void
    {
        /** @var int<1, max> $contextLines */
        $contextLines = ConfigurationRegistry::get()->diffContext();

        ComparatorFactory::getInstance()->setContextLines(
            $contextLines,
        );
    }
}
