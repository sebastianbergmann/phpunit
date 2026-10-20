<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;

final class SocketServer extends EventEmitter implements ServerInterface
{
    private $server;

    /**
     * The `SocketServer` class is the main class in this package that implements the `ServerInterface` and
     * allows you to accept incoming streaming connections, such as plaintext TCP/IP or secure TLS connection streams.
     *
     * ```php
     * $socket = new React\Socket\SocketServer('127.0.0.1:0');
     * $socket = new React\Socket\SocketServer('127.0.0.1:8000');
     * $socket = new React\Socket\SocketServer('127.0.0.1:8000', $context);
     * ```
     *
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * @param string         $uri
     * @param array          $context
     * @param ?LoopInterface $loop
     * @throws \InvalidArgumentException if the listening address is invalid
     * @throws \RuntimeException if listening on this address fails (already in use etc.)
     */
    public function __construct($uri, array $context = array(), $loop = null)
    {
        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #3 ($loop) expected null|React\EventLoop\LoopInterface');
        }

        // apply default options if not explicitly given
        $context += array(
            'tcp' => array(),
            'tls' => array(),
            'unix' => array()
        );

        $scheme = 'tcp';
        $pos = \strpos($uri, '://');
        if ($pos !== false) {
            $scheme = \substr($uri, 0, $pos);
        }

        if ($scheme === 'unix') {
            $server = new UnixServer($uri, $loop, $context['unix']);
        } elseif ($scheme === 'php') {
            $server = new FdServer($uri, $loop);
        } else {
            if (preg_match('#^(?:\w+://)?\d+$#', $uri)) {
                throw new \InvalidArgumentException(
                    'Invalid URI given (EINVAL)',
                    \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : (\defined('PCNTL_EINVAL') ? \PCNTL_EINVAL : 22)
                );
            }

            $server = new TcpServer(str_replace('tls://', '', $uri), $loop, $context['tcp']);

            if ($scheme === 'tls') {
                $server = new SecureServer($server, $loop, $context['tls']);
            }
        }

        $this->server = $server;

        $that = $this;
        $server->on('connection', function (ConnectionInterface $conn) use ($that) {
            $that->emit('connection', array($conn));
        });
        $server->on('error', function (\Exception $error) use ($that) {
            $that->emit('error', array($error));
        });
    }

    public function getAddress()
    {
        return $this->server->getAddress();
    }

    public function pause()
    {
        $this->server->pause();
    }

    public function resume()
    {
        $this->server->resume();
    }

    public function close()
    {
        $this->server->close();
    }

    /**
     * [internal] Internal helper method to accept new connection from given server socket
     *
     * @param resource $socket server socket to accept connection from
     * @return resource new client socket if any
     * @throws \RuntimeException if accepting fails
     * @internal
     */
    public static function accept($socket)
    {
        $errno = 0;
        $errstr = '';
        \set_error_handler(function ($_, $error) use (&$errno, &$errstr) {
            // Match errstr from PHP's warning message.
            // stream_socket_accept(): accept failed: Connection timed out
            $errstr = \preg_replace('#.*: #', '', $error);
            $errno = SocketServer::errno($errstr);
        });

        $newSocket = \stream_socket_accept($socket, 0);

        \restore_error_handler();

        if (false === $newSocket) {
            throw new \RuntimeException(
                'Unable to accept new connection: ' . $errstr . self::errconst($errno),
                $errno
            );
        }

        return $newSocket;
    }

    /**
     * [Internal] Returns errno value for given errstr
     *
     * The errno and errstr values describes the type of error that has been
     * encountered. This method tries to look up the given errstr and find a
     * matching errno value which can be useful to provide more context to error
     * messages. It goes through the list of known errno constants when either
     * `ext-sockets`, `ext-posix` or `ext-pcntl` is available to find an errno
     * matching the given errstr.
     *
     * @param string $errstr
     * @return int errno value (e.g. value of `SOCKET_ECONNREFUSED`) or 0 if not found
     * @internal
     * @copyright Copyright (c) 2023 Christian Lück, taken from https://github.com/clue/errno with permission
     * @codeCoverageIgnore
     */
    public static function errno($errstr)
    {
        // PHP defines the required `strerror()` function through either `ext-sockets`, `ext-posix` or `ext-pcntl`
        $strerror = \function_exists('socket_strerror') ? 'socket_strerror' : (\function_exists('posix_strerror') ? 'posix_strerror' : (\function_exists('pcntl_strerror') ? 'pcntl_strerror' : null));
        if ($strerror !== null) {
            assert(\is_string($strerror) && \is_callable($strerror));

            // PHP defines most useful errno constants like `ECONNREFUSED` through constants in `ext-sockets` like `SOCKET_ECONNREFUSED`
            // PHP also defines a hand full of errno constants like `EMFILE` through constants in `ext-pcntl` like `PCNTL_EMFILE`
            // go through list of all defined constants like `SOCKET_E*` and `PCNTL_E*` and see if they match the given `$errstr`
            foreach (\get_defined_constants(false) as $name => $value) {
                if (\is_int($value) && (\strpos($name, 'SOCKET_E') === 0 || \strpos($name, 'PCNTL_E') === 0) && $strerror($value) === $errstr) {
                    return $value;
                }
            }

            // if we reach this, no matching errno constant could be found (unlikely when `ext-sockets` is available)
            // go through list of all possible errno values from 1 to `MAX_ERRNO` and see if they match the given `$errstr`
            for ($errno = 1, $max = \defined('MAX_ERRNO') ? \MAX_ERRNO : 4095; $errno <= $max; ++$errno) {
                if ($strerror($errno) === $errstr) {
                    return $errno;
                }
            }
        }

        // if we reach this, no matching errno value could be found (unlikely when either `ext-sockets`, `ext-posix` or `ext-pcntl` is available)
        return 0;
    }

    /**
     * [Internal] Returns errno constant name for given errno value
     *
     * The errno value describes the type of error that has been encountered.
     * This method tries to look up the given errno value and find a matching
     * errno constant name which can be useful to provide more context and more
     * descriptive error messages. It goes through the list of known errno
     * constants when either `ext-sockets` or `ext-pcntl` is available to find
     * the matching errno constant name.
     *
     * Because this method is used to append more context to error messages, the
     * constant name will be prefixed with a space and put between parenthesis
     * when found.
     *
     * @param int $errno
     * @return string e.g. ` (ECONNREFUSED)` or empty string if no matching const for the given errno could be found
     * @internal
     * @copyright Copyright (c) 2023 Christian Lück, taken from https://github.com/clue/errno with permission
     * @codeCoverageIgnore
     */
    public static function errconst($errno)
    {
        // PHP defines most useful errno constants like `ECONNREFUSED` through constants in `ext-sockets` like `SOCKET_ECONNREFUSED`
        // PHP also defines a hand full of errno constants like `EMFILE` through constants in `ext-pcntl` like `PCNTL_EMFILE`
        // go through list of all defined constants like `SOCKET_E*` and `PCNTL_E*` and see if they match the given `$errno`
        foreach (\get_defined_constants(false) as $name => $value) {
            if ($value === $errno && (\strpos($name, 'SOCKET_E') === 0 || \strpos($name, 'PCNTL_E') === 0)) {
                return ' (' . \substr($name, \strpos($name, '_') + 1) . ')';
            }
        }

        // if we reach this, no matching errno constant could be found (unlikely when `ext-sockets` is available)
        return '';
    }
}
