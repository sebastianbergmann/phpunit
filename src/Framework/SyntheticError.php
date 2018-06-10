<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

/**
 * Creates a synthetic failed assertion.
 */
class SyntheticError extends AssertionFailedError
{
    /**
     * The synthetic file.
     *
     * @var string
     */
    protected $syntheticFile = '';

    /**
     * The synthetic line number.
     *
     * @var int
     */
    protected $syntheticLine = 0;

    /**
     * The synthetic trace.
     *
     * @var array
     */
    protected $syntheticTrace = [];

    public function __construct(string $message, int $code, string $file, int $line, array $trace)
    {
        parent::__construct($message, $code);

        $this->syntheticFile  = $file;
        $this->syntheticLine  = $line;
        $this->syntheticTrace = $trace;
    }

    public function getSyntheticFile(): string
    {
        return $this->syntheticFile;
    }

    public function getSyntheticLine(): int
    {
        return $this->syntheticLine;
    }

    public function getSyntheticTrace(): array
    {
        return $this->syntheticTrace;
    }
}
