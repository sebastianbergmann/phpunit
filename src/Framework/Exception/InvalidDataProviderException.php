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

use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvalidDataProviderException extends Exception
{
    private ?string $providerLabel = null;

    public static function forException(Throwable $e, string $providerLabel): self
    {
        $exception = new self(
            $e->getMessage(),
            $e->getCode(),
            $e,
        );
        $exception->providerLabel = $providerLabel;

        return $exception;
    }

    public function getProviderLabel(): ?string
    {
        return $this->providerLabel;
    }
}
