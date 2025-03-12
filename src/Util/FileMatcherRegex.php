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

use function preg_match;
use function sprintf;
use function substr;
use RuntimeException;

final class FileMatcherRegex
{
    public function __construct(private string $regex)
    {
    }

    public function matches(string $path): bool
    {
        self::assertIsAbsolute($path);

        return preg_match($this->regex, $path) !== 0;
    }

    private static function assertIsAbsolute(string $path): void
    {
        if (substr($path, 0, 1) !== '/') {
            throw new RuntimeException(sprintf(
                'Path "%s" must be absolute',
                $path,
            ));
        }
    }
}
