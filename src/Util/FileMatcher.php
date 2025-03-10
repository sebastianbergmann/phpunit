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

use InvalidArgumentException;
use RuntimeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FileMatcher
{
    public static function match(string $path, FileMatcherPattern $pattern): bool
    {
        self::assertIsAbsolute($path);

        $regex = self::toRegEx($pattern->path);
        dump($pattern->path, $regex, $path);

        return preg_match($regex, $path) !== 0;
    }

    /**
     * Based on webmozart/glob
     *
     * @return string The regular expression for matching the glob.
     */
    public static function toRegEx($glob, $flags = 0): string
    {
        self::assertIsAbsolute($glob);

        $inSquare = false;
        $regex = '';
        $length = strlen($glob);

        for ($i = 0; $i < $length; ++$i) {
            $c = $glob[$i];

            switch ($c) {
                case '?':
                    $regex .= '.';
                    break;

                // the PHPUnit file iterator will match all
                // files within a wildcard, not just until the
                // next directory separator
                case '*':
                    // if this is a ** but it is NOT preceded with `/` then
                    // it is not a globstar and just interpret it as a literal
                    if (($glob[$i + 1] ?? null) === '*') {
                        $regex .= '\*\*';
                        $i++;
                        break;
                    }
                    $regex .= '.*';
                    break;
                case '/':
                    if (isset($glob[$i + 3]) && '**/' === $glob[$i + 1].$glob[$i + 2].$glob[$i + 3]) {
                        $regex .= '/([^/]+/)*';
                        $i += 3;
                        break;
                    }
                    if ((!isset($glob[$i + 3])) && isset($glob[$i + 2]) && '**' === $glob[$i + 1].$glob[$i + 2]) {
                        $regex .= '.*';
                        $i += 2;
                        break;
                    }
                    $regex .= '/';
                    break;
                default:
                    $regex .= $c;
                    break;
            }
        }

        if ($inSquare) {
            throw new InvalidArgumentException(sprintf(
                'Invalid glob: missing ] in %s',
                $glob
            ));
        }

        $regex .= '(/|$)';

        return '{^'.$regex.'}';
    }

    private static function assertIsAbsolute(string $path): void
    {
        if (substr($path, 0, 1) !== '/') {
            throw new RuntimeException(sprintf(
                'Path "%s" must be absolute',
                $path
            ));
        }
    }
}

