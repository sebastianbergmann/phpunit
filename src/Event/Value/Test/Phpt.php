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
     * @var positive-int
     */
    private int $repeatAttemptNumber;

    /**
     * @param non-empty-string $file
     * @param positive-int     $repeatAttemptNumber
     */
    public function __construct(string $file, int $repeatAttemptNumber = 1)
    {
        parent::__construct($file);

        $this->repeatAttemptNumber = $repeatAttemptNumber;
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
        return $this->name();
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        if ($this->repeatAttemptNumber === 1) {
            return $this->file();
        }

        return $this->file() . " (repeat attempt #{$this->repeatAttemptNumber})";
    }
}
