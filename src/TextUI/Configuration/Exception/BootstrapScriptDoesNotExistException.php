<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function sprintf;
use RuntimeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class BootstrapScriptDoesNotExistException extends RuntimeException implements Exception
{
    public function __construct(string $filename)
    {
        parent::__construct(
            sprintf(
                'Cannot open bootstrap script "%s"',
                $filename,
            ),
        );
    }
}
