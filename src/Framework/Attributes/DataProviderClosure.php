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
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class DataProviderClosure
{
    private \Closure $closure;

    private bool $validateArgumentCount;

    public function __construct(\Closure $closure, bool $validateArgumentCount = true)
    {
        $this->closure               = $closure;
        $this->validateArgumentCount = $validateArgumentCount;
    }

    public function closure(): \Closure
    {
        return $this->closure;
    }

    public function validateArgumentCount(): bool
    {
        return $this->validateArgumentCount;
    }
}
