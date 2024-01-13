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

use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Util\Filter;
use PHPUnit\Util\ThrowableToStringMapper;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ThrowableBuilder
{
    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    public static function from(\Throwable $t): Throwable
    {
        $previous = $t->getPrevious();

        if ($previous !== null) {
            $previous = self::from($previous);
        }

        return new Throwable(
            $t::class,
            $t->getMessage(),
            ThrowableToStringMapper::map($t),
            Filter::getFilteredStacktrace($t, false),
            $previous,
        );
    }
}
