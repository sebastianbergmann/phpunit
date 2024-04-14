<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class RunInSeparateProcess
{
    private ?bool $forkIfPossible;

    public function __construct(?bool $forkIfPossible = null)
    {
        $this->forkIfPossible = $forkIfPossible;
    }

    public function forkIfPossible(): ?bool
    {
        return $this->forkIfPossible;
    }
}
