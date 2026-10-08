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
final readonly class Property extends Metadata
{
    /**
     * @var ?positive-int
     */
    private ?int $examples;

    /**
     * @param ?positive-int $examples
     */
    protected function __construct(Level $level, ?int $examples)
    {
        parent::__construct($level);

        $this->examples = $examples;
    }

    public function isProperty(): true
    {
        return true;
    }

    /**
     * The number of examples to check, or null for the configured default.
     *
     * @return ?positive-int
     */
    public function examples(): ?int
    {
        return $this->examples;
    }
}
