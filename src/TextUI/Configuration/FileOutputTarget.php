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

/**
 * A path that PHPUnit would write to during the test run.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FileOutputTarget
{
    /**
     * @var non-empty-string
     */
    private string $description;

    /**
     * @var non-empty-string
     */
    private string $path;

    /**
     * @param non-empty-string $description
     * @param non-empty-string $path
     */
    public function __construct(string $description, string $path)
    {
        $this->description = $description;
        $this->path        = $path;
    }

    /**
     * @return non-empty-string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return non-empty-string
     */
    public function path(): string
    {
        return $this->path;
    }
}
