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
 * @psalm-immutable
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class IgnorePhpunitDeprecations extends Metadata
{
    /**
     * @psalm-assert-if-true IgnorePhpunitDeprecations $this
     */
    public function isIgnorePhpunitDeprecations(): bool
    {
        return true;
    }
}
