<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\CliArguments;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Mapper
{
    /**
     * @throws Exception
     */
    public function mapToLegacyArray(Configuration $arguments): array
    {
        $result = [
            'extensions'            => [],
            'unavailableExtensions' => [],
        ];

        if ($arguments->hasExtensions()) {
            $result['extensions'] = $arguments->extensions();
        }

        if ($arguments->hasUnavailableExtensions()) {
            $result['unavailableExtensions'] = $arguments->unavailableExtensions();
        }

        if ($arguments->hasNoOutput()) {
            $result['noOutput'] = $arguments->noOutput();
        }

        if ($arguments->hasCoverageFilter()) {
            $result['coverageFilter'] = $arguments->coverageFilter();
        }

        return $result;
    }
}
