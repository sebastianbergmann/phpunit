<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function assert;
use function base64_decode;
use function base64_encode;
use function is_array;
use function is_string;
use function serialize;
use function unserialize;

/**
 * The wire format with which the parent process sends commands to a worker on
 * the worker's control channel, its standard input.
 *
 * A command is an array that carries the name of the command in its 'command'
 * key, the files through which the worker reports the result of the command,
 * and, for a command that runs a unit of work, the descriptors of the unit's
 * members. It is serialized, so that the descriptors travel as descriptors and
 * are reconstituted as such in the worker, and base64-encoded, so that the
 * payload cannot contain the newline that delimits one command from the next
 * on the control channel.
 *
 * A command is decoded with the classes of the descriptors as the only classes
 * allowed in the payload. The provided data and the dependency input of a test
 * case are therefore not unserialized here: they travel as the strings into
 * which the parent process serialized them and are unserialized, with the
 * classes they contain, only when the test case they belong to is rebuilt (see
 * TestCaseDescriptor).
 *
 * Encoding and decoding live side by side in this class so that the two
 * directions of the control channel cannot drift apart.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CommandStream
{
    /**
     * Encode one command for transport on the control channel. The newline
     * that delimits it from the next command is not part of the result.
     *
     * @param array<string, mixed> $command
     *
     * @return non-empty-string
     */
    public static function encode(array $command): string
    {
        $encoded = base64_encode(serialize($command));

        assert($encoded !== '');

        return $encoded;
    }

    /**
     * Decode one command received on the control channel.
     *
     * Null is returned when the line does not decode to a command, which means
     * that the control channel was written to by an unexpected process or was
     * tampered with; the worker then has nothing it could execute and stops.
     *
     * @return ?array<mixed>
     */
    public static function decode(string $line): ?array
    {
        $decoded = base64_decode($line, true);

        if ($decoded === false) {
            return null;
        }

        $command = @unserialize(
            $decoded,
            [
                'allowed_classes' => [
                    DataProviderSuiteDescriptor::class,
                    RepeatSuiteDescriptor::class,
                    RetrySuiteDescriptor::class,
                    TestCaseDescriptor::class,
                ],
            ],
        );

        if (!is_array($command) || !isset($command['command']) || !is_string($command['command'])) {
            return null;
        }

        return $command;
    }
}
