<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

/**
 * [Internal] The `FdServer` class implements the `ServerInterface` and
 * is responsible for accepting connections from an existing file descriptor.
 *
 * ```php
 * $socket = new React\Socket\FdServer(3);
 * ```
 *
 * Whenever a client connects, it will emit a `connection` event with a connection
 * instance implementing `ConnectionInterface`:
 *
 * ```php
 * $socket->on('connection', function (ConnectionInterface $connection) {
 *     echo 'Plaintext connection from ' . $connection->getRemoteAddress() . PHP_EOL;
 *     $connection->write('hello there!' . PHP_EOL);
 *     …
 * });
 * ```
 *
 * See also the `ServerInterface` for more details.
 *
 * @see ServerInterface
 * @see ConnectionInterface
 * @internal
 */
final class FdServer extends EventEmitter implements ServerInterface
{
    private $master;
    private $loop;
    private $unix = false;
    private $listening = false;

    /**
     * Creates a socket server and starts listening on the given file descriptor
     *
     * This starts accepting new incoming connections on the given file descriptor.
     * See also the `connection event` documented in the `ServerInterface`
     * for more details.
     *
     * ```php
     * $socket = new React\Socket\FdServer(3);
     * ```
     *
     * If the given FD is invalid or out of range, it will throw an `InvalidArgumentException`:
     *
     * ```php
     * // throws InvalidArgumentException
     * $socket = new React\Socket\FdServer(-1);
     * ```
     *
     * If the given FD appears to be valid, but listening on it fails (such as
     * if the FD does not exist or does not refer to a socket server), it will
     * throw a `RuntimeException`:
     *
     * ```php
     * // throws RuntimeException because FD does not reference a socket server
     * $socket = new React\Socket\FdServer(0, $loop);
     * ```
     *
     * Note that these error conditions may vary depending on your system and/or
     * configuration.
     * See the exception message and code for more details about the actual error
     * condition.
     *
     * @param int|string     $fd   FD number such as `3` or as URL in the form of `php://fd/3`
     * @param ?LoopInterface $loop
     * @throws \InvalidArgumentException if the listening address is invalid
     * @throws \RuntimeException if listening on this address fails (already in use etc.)
     */
    public function __construct($fd, $loop = null)
    {
        if (\preg_match('#^php://fd/(\d+)$#', $fd, $m)) {
            $fd = (int) $m[1];
        }
        if (!\is_int($fd) || $fd < 0 || $fd >= \PHP_INT_MAX) {
            throw new \InvalidArgumentException(
                'Invalid FD number given (EINVAL)',
                \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : (\defined('PCNTL_EINVAL') ? \PCNTL_EINVAL : 22)
            );
        }

        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #2 ($loop) expected null|React\EventLoop\LoopInterface');
        }

        $this->loop = $loop ?: Loop::get();

        $errno = 0;
        $errstr = '';
        \set_error_handler(function ($_, $error) use (&$errno, &$errstr) {
            // Match errstr from PHP's warning message.
            // fopen(php://fd/3): Failed to open stream: Error duping file descriptor 3; possibly it doesn't exist: [9]: Bad file descriptor
            \preg_match('/\[(\d+)\]: (.*)/', $error, $m);
            $errno = isset($m[1]) ? (int) $m[1] : 0;
            $errstr = isset($m[2]) ? $m[2] : $error;
        });

        $this->master = \fopen('php://fd/' . $fd, 'r+');

        \restore_error_handler();

        if (false === $this->master) {
            throw new \RuntimeException(
                'Failed to listen on FD ' . $fd . ': ' . $errstr . SocketServer::errconst($errno),
                $errno
            );
        }

        $meta = \stream_get_meta_data($this->master);
        if (!isset($meta['stream_type']) || $meta['stream_type'] !== 'tcp_socket') {
            \fclose($this->master);

            $errno = \defined('SOCKET_ENOTSOCK') ? \SOCKET_ENOTSOCK : 88;
            $errstr = \function_exists('socket_strerror') ? \socket_strerror($errno) : 'Not a socket';

            throw new \RuntimeException(
                'Failed to listen on FD ' . $fd . ': ' . $errstr . ' (ENOTSOCK)',
                $errno
            );
        }

        // Socket should not have a peer address if this is a listening socket.
        // Looks like this work-around is the closest we can get because PHP doesn't expose SO_ACCEPTCONN even with ext-sockets.
        if (\stream_socket_get_name($this->master, true) !== false) {
            \fclose($this->master);

            $errno = \defined('SOCKET_EISCONN') ? \SOCKET_EISCONN : 106;
            $errstr = \function_exists('socket_strerror') ? \socket_strerror($errno) : 'Socket is connected';

            throw new \RuntimeException(
                'Failed to listen on FD ' . $fd . ': ' . $errstr . ' (EISCONN)',
                $errno
            );
        }

        // Assume this is a Unix domain socket (UDS) when its listening address doesn't parse as a valid URL with a port.
        // Looks like this work-around is the closest we can get because PHP doesn't expose SO_DOMAIN even with ext-sockets.
        $this->unix = \parse_url($this->getAddress(), \PHP_URL_PORT) === false;

        \stream_set_blocking($this->master, false);

        $this->resume();
    }

    public function getAddress()
    {
        if (!\is_resource($this->master)) {
            return null;
        }

        $address = \stream_socket_get_name($this->master, false);

        if ($this->unix === true) {
            return 'unix://' . $address;
        }

        // check if this is an IPv6 address which includes multiple colons but no square brackets
        $pos = \strrpos($address, ':');
        if ($pos !== false && \strpos($address, ':') < $pos && \substr($address, 0, 1) !== '[') {
            $address = '[' . \substr($address, 0, $pos) . ']:' . \substr($address, $pos + 1); // @codeCoverageIgnore
        }

        return 'tcp://' . $address;
    }

    public function pause()
    {
        if (!$this->listening) {
            return;
        }

        $this->loop->removeReadStream($this->master);
        $this->listening = false;
    }

    public function resume()
    {
        if ($this->listening || !\is_resource($this->master)) {
            return;
        }

        $that = $this;
        $this->loop->addReadStream($this->master, function ($master) use ($that) {
            try {
                $newSocket = SocketServer::accept($master);
            } catch (\RuntimeException $e) {
                $that->emit('error', array($e));
                return;
            }
            $that->handleConnection($newSocket);
        });
        $this->listening = true;
    }

    public function close()
    {
        if (!\is_resource($this->master)) {
            return;
        }

        $this->pause();
        \fclose($this->master);
        $this->removeAllListeners();
    }

    /** @internal */
    public function handleConnection($socket)
    {
        $connection = new Connection($socket, $this->loop);
        $connection->unix = $this->unix;

        $this->emit('connection', array($connection));
    }
}
