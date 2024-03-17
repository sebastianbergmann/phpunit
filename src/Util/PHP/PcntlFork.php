<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;

final class PcntlFork {
    // IPC inspired from https://github.com/barracudanetworks/forkdaemon-php
    private const SOCKET_HEADER_SIZE = 4;

    static public function isPcntlForkAvailable(): bool {
        $disabledFunctions = ini_get('disable_functions');

        return
            function_exists('pcntl_fork')
            && !str_contains($disabledFunctions, 'pcntl')
            && function_exists('socket_create_pair')
            && !str_contains($disabledFunctions, 'socket')
        ;
    }

    public function runTest(TestCase $test): void
    {
        list($socket_child, $socket_parent) = $this->ipcInit();

        $pid = pcntl_fork();

        if ($pid === -1 ) {
            throw new \Exception('could not fork');
        } else if ($pid) {
            // we are the parent

            socket_close($socket_parent);

            // read child stdout, stderr
            $result = $this->socketReceive($socket_child);

            $stderr = '';
            $stdout = '';
            if (is_array($result) && array_key_exists('error', $result)) {
                $stderr = $result['error'];
            } else {
                $stdout = $result;
            }

            $php = AbstractPhpProcess::factory();
            $php->processChildResult($test, $stdout, $stderr);

        } else {
            // we are the child

            socket_close($socket_child);

            $offset                  = hrtime();
            $dispatcher = Facade::instance()->initForIsolation(
                \PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
                    $offset[0],
                    $offset[1]
                )
            );

            $test->setInIsolation(true);
            try {
                $test->run();
            } catch (Throwable $e) {
                $this->socketSend($socket_parent, ['error' => $e->getMessage()]);
                exit();
            }

            $result = serialize(
                [
                    'testResult'    => $test->result(),
                    'codeCoverage'  => CodeCoverage::instance()->isActive() ? CodeCoverage::instance()->codeCoverage() : null,
                    'numAssertions' => $test->numberOfAssertionsPerformed(),
                    'output'        => !$test->expectsOutput() ? $test->output() : '',
                    'events'        => $dispatcher->flush(),
                    'passedTests'   => PassedTests::instance()
                ]
            );

            // send result into parent
            $this->socketSend($socket_parent, $result);
            exit();
        }
    }

    private function ipcInit(): array
    {
        // windows needs AF_INET
        $domain = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? AF_INET : AF_UNIX;

        // create a socket pair for IPC
        $sockets = array();
        if (socket_create_pair($domain, SOCK_STREAM, 0, $sockets) === false)
        {
            throw new \RuntimeException('socket_create_pair failed: ' . socket_strerror(socket_last_error()));
        }

        return $sockets;
    }

    /**
     * @param resource $socket
     */
    private function socketReceive($socket): mixed
    {
        // initially read to the length of the header size, then
        // expand to read more
        $bytes_total = self::SOCKET_HEADER_SIZE;
        $bytes_read = 0;
        $have_header = false;
        $socket_message = '';
        while ($bytes_read < $bytes_total)
        {
            $read = @socket_read($socket, $bytes_total - $bytes_read);
            if ($read === false)
            {
                throw new \RuntimeException('socket_receive error: ' . socket_strerror(socket_last_error()));
            }

            // blank socket_read means done
            if ($read == '')
            {
                break;
            }

            $bytes_read += strlen($read);
            $socket_message .= $read;

            if (!$have_header && $bytes_read >= self::SOCKET_HEADER_SIZE)
            {
                $have_header = true;
                list($bytes_total) = array_values(unpack('N', $socket_message));
                $bytes_read = 0;
                $socket_message = '';
            }
        }

        return @unserialize($socket_message);
    }

    /**
     * @param resource $socket
     * @param mixed $message
     */
    private function socketSend($socket, $message): void
    {
        $serialized_message = @serialize($message);
        if ($serialized_message == false)
        {
            throw new \RuntimeException('socket_send failed to serialize message');
        }

        $header = pack('N', strlen($serialized_message));
        $data = $header . $serialized_message;
        $bytes_left = strlen($data);
        while ($bytes_left > 0)
        {
            $bytes_sent = @socket_write($socket, $data);
            if ($bytes_sent === false)
            {
                throw new \RuntimeException('socket_send failed to write to socket');
            }

            $bytes_left -= $bytes_sent;
            $data = substr($data, $bytes_sent);
        }
    }
}
