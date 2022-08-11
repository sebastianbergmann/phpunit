<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use const PHP_EOL;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ActualValueIsNotAnObjectException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Actual value is not an object'
        );
    }

    public function __toString(): string
    {
        return $this->getMessage() . PHP_EOL;
    }
}
