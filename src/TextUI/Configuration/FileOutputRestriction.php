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

use PHPUnit\Util\Filesystem;

/**
 * The directory that --restrict-file-output allows writing to.
 *
 * Streams are not files: php://stdout and php://stderr are always allowed,
 * every other stream is always rejected.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FileOutputRestriction
{
    /**
     * @var non-empty-string
     */
    private string $directory;

    /**
     * @param non-empty-string $directory the resolved path of the directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return non-empty-string
     */
    public function directory(): string
    {
        return $this->directory;
    }

    /**
     * @param non-empty-string $path
     */
    public function allows(string $path): bool
    {
        if ($path === 'php://stdout' || $path === 'php://stderr') {
            return true;
        }

        if (Filesystem::isStream($path)) {
            return false;
        }

        return Filesystem::isInside(Filesystem::resolvePath($path), $this->directory);
    }

    /**
     * @param list<FileOutputTarget> $targets
     *
     * @return list<FileOutputTarget>
     */
    public function violations(array $targets): array
    {
        $violations = [];

        foreach ($targets as $target) {
            if (!$this->allows($target->path())) {
                $violations[] = $target;
            }
        }

        return $violations;
    }
}
