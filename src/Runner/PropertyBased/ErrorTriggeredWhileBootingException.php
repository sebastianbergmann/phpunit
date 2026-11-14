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
final class ErrorTriggeredWhileBootingException extends RuntimeException implements Exception
{
    /**
     * @param non-empty-list<array{test: non-empty-string, message: string}> $errors the ID of the test for which an error was triggered, and its message
     */
    public function __construct(array $errors)
    {
        $descriptions = [];

        foreach ($errors as $number => $error) {
            $descriptions[] = sprintf(
                "%d) %s\n%s",
                $number + 1,
                $error['test'],
                $error['message'],
            );
        }

        if (count($errors) === 1) {
            $numberOfErrors = 'an error';
        } else {
            $numberOfErrors = count($errors) . ' errors';
        }

        parent::__construct(
            sprintf(
                "Booting PHPUnit triggered %s:\n\n%s",
                $numberOfErrors,
                implode("\n\n", $descriptions),
            ),
        );
    }
}
