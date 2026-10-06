<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class UsesFixture extends Metadata
{
    /**
     * @var non-empty-string
     */
    private string $path;

    /**
     * @var non-empty-string
     */
    private string $declaringFile;

    /**
     * @param non-empty-string $path
     * @param non-empty-string $declaringFile
     */
    protected function __construct(Level $level, string $path, string $declaringFile)
    {
        parent::__construct($level);

        $this->path          = $path;
        $this->declaringFile = $declaringFile;
    }

    public function isUsesFixture(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * The file the attribute is written in, which the path is relative to:
     * for an attribute that is inherited from a parent class, or that is
     * declared on a method of a trait, this is the file of that parent class
     * or of that trait.
     *
     * @return non-empty-string
     */
    public function declaringFile(): string
    {
        return $this->declaringFile;
    }
}
