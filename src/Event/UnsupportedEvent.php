<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use Exception;

final class UnsupportedEvent extends Exception
{
    public static function type(Type $type): self
    {
        return new self(sprintf(
            'Type "%s" not supported',
            $type->asString()
        ));
    }
}
