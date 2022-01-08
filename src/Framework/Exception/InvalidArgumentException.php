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

use function debug_backtrace;
use function in_array;
use function lcfirst;
use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvalidArgumentException extends Exception
{
    public static function create(int $argument, string $type): self
    {
        $stack    = debug_backtrace();
        $function = $stack[1]['function'];

        if (isset($stack[1]['class'])) {
            $function = sprintf('%s::%s', $stack[1]['class'], $stack[1]['function']);
        }

        return new self(
            sprintf(
                'Argument #%d of %s() must be %s %s',
                $argument,
                $function,
                in_array(lcfirst($type)[0], ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a',
                $type
            )
        );
    }

    private function __construct(string $message = '', int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
