<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Phpt extends Test
{
    /**
     * @var ?non-empty-string
     */
    private ?string $description;

    /**
     * @param non-empty-string  $file
     * @param ?non-empty-string $description
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(string $file, ?string $description)
    {
        parent::__construct($file);

        $this->description = $description;
    }

    public function isPhpt(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function id(): string
    {
        return $this->file();
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->file();
    }

    /**
     * @phpstan-assert-if-true !null $this->description
     */
    public function hasDescription(): bool
    {
        return $this->description !== null;
    }

    /**
     * @throws NoDescriptionException
     *
     * @return non-empty-string
     */
    public function description(): string
    {
        if ($this->description === null) {
            throw new NoDescriptionException;
        }

        return $this->description;
    }
}
