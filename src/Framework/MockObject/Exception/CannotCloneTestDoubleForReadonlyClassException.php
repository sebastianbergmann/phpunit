<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final class CannotCloneTestDoubleForReadonlyClassException extends \PHPUnit\Framework\Exception implements Exception
{
    public function __construct()
    {
        parent::__construct(
            'Cloning test doubles for readonly classes is not supported on PHP 8.2',
        );
    }
}
