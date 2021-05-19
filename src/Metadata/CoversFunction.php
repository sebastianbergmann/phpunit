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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class CoversFunction extends Metadata
{
    private string $functionName;

    public function __construct(string $functionName)
    {
        $this->functionName = $functionName;
    }

    public function isCoversFunction(): bool
    {
        return true;
    }

    public function functionName(): string
    {
        return $this->functionName;
    }

    public function asStringForCodeUnitMapper(): string
    {
        return '::' . $this->functionName;
    }
}
