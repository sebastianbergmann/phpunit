<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use function array_key_exists;
use function hash_file;
use function is_file;

/**
 * Hashes the contents of source files, remembering the hashes it computed.
 *
 * The contents of a file, and not its modification time and size, decide
 * whether an entry of the test index is still valid: renaming a group, for
 * instance, does not necessarily change the size of a file and the resolution
 * of the modification time of a file is one second on many file systems.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class FileHasher
{
    /**
     * @var array<non-empty-string, ?non-empty-string>
     */
    private array $hashes = [];

    /**
     * Returns null when the file does not exist or cannot be read.
     *
     * @param non-empty-string $file
     *
     * @return ?non-empty-string
     */
    public function hash(string $file): ?string
    {
        if (array_key_exists($file, $this->hashes)) {
            return $this->hashes[$file];
        }

        $hash = null;

        if (is_file($file)) {
            $result = @hash_file('xxh128', $file);

            if ($result !== false && $result !== '') {
                $hash = $result;
            }
        }

        $this->hashes[$file] = $hash;

        return $hash;
    }
}
