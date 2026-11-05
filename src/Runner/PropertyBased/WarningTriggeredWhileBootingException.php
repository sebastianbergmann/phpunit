<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\PropertyBased;

use function count;
use function implode;
use function sprintf;
use PHPUnit\Exception;
use RuntimeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class WarningTriggeredWhileBootingException extends RuntimeException implements Exception
{
    /**
     * @param non-empty-list<string> $messages
     */
    public function __construct(array $messages)
    {
        $warnings = [];

        foreach ($messages as $number => $message) {
            $warnings[] = sprintf('%d) %s', $number + 1, $message);
        }

        parent::__construct(
            sprintf(
                "Booting PHPUnit triggered %s:\n\n%s",
                count($messages) === 1 ? 'a warning' : count($messages) . ' warnings',
                implode("\n\n", $warnings),
            ),
        );
    }
}
