<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * The `TcpServer` class implements the `ServerInterface` and
 * is responsible for accepting plaintext TCP/IP connections.
 *
 * ```php
 * $server = new React\Socket\TcpServer(8080);
 * ```
 *
 * Whenever a client connects, it will emit a `connection` event with a connection
 * instance implementing `ConnectionInterface`:
 *
 * ```php
 * $server->on('connection', function (React\Socket\ConnectionInterface $connection) {
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
 */
final class TcpServer extends EventEmitter implements ServerInterface
{
    private $master;
    private $loop;
    private $listening = false;

    /**
     * Creates a plaintext TCP/IP socket server and starts listening on the given address
     *
     * This starts accepting new incoming connections on the given address.
     * See also the `connection event` documented in the `ServerInterface`
     * for more details.
     *
     * ```php
     * $server = new React\Socket\TcpServer(8080);
     * ```
     *
     * As above, the `$uri` parameter can consist of only a port, in which case the
     * server will default to listening on the localhost address `127.0.0.1`,
     * which means it will not be reachable from outside of this system.
     *
     * In order to use a random port assignment, you can use the port `0`:
     *
     * ```php
     * $server = new React\Socket\TcpServer(0);
     * $address = $server->getAddress();
     * ```
     *
     * In order to change the host the socket is listening on, you can provide an IP
     * address through the first parameter provided to the constructor, optionally
     * preceded by the `tcp://` scheme:
     *
     * ```php
     * $server = new React\Socket\TcpServer('192.168.0.1:8080');
     * ```
     *
     * If you want to listen on an IPv6 address, you MUST enclose the host in square
     * brackets:
     *
     * ```php
     * $server = new React\Socket\TcpServer('[::1]:8080');
     * ```
     *
     * If the given URI is invalid, does not contain a port, any other scheme or if it
     * contains a hostname, it will throw an `InvalidArgumentException`:
     *
     * ```php
     * // throws InvalidArgumentException due to missing port
     * $server = new React\Socket\TcpServer('127.0.0.1');
     * ```
     *
     * If the given URI appears to be valid, but listening on it fails (such as if port
     * is already in use or port below 1024 may require root access etc.), it will
     * throw a `RuntimeException`:
     *
     * ```php
     * $first = new React\Socket\TcpServer(8080);
     *
     * // throws RuntimeException because port is already in use
     * $second = new React\Socket\TcpServer(8080);
     * ```
     *
     * Note that these error conditions may vary depending on your system and/or
     * configuration.
     * See the exception message and code for more details about the actual error
     * condition.
     *
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * Optionally, you can specify [socket context options](https://www.php.net/manual/en/context.socket.php)
     * for the underlying stream socket resource like this:
     *
     * ```php
     * $server = new React\Socket\TcpServer('[::1]:8080', null, array(
     *     'backlog' => 200,
     *     'so_reuseport' => true,
     *     'ipv6_v6only' => true
     * ));
     * ```
     *
     * Note that available [socket context options](https://www.php.net/manual/en/context.socket.php),
     * their defaults and effects of changing these may vary depending on your system
     * and/or PHP version.
     * Passing unknown context options has no effect.
     * The `backlog` context option defaults to `511` unless given explicitly.
     *
     * @param string|int     $uri
     * @param ?LoopInterface $loop
     * @param array          $context
     * @throws InvalidArgumentException if the listening address is invalid
     * @throws RuntimeException if listening on this address fails (already in use etc.)
     */
    public function __construct($uri, $loop = null, array $context = array())
    {
        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #2 ($loop) expected null|React\EventLoop\LoopInterface');
        }

        $this->loop = $loop ?: Loop::get();

        // a single port has been given => assume localhost
        if ((string)(int)$uri === (string)$uri) {
            $uri = '127.0.0.1:' . $uri;
        }

        // assume default scheme if none has been given
        if (\strpos($uri, '://') === false) {
            $uri = 'tcp://' . $uri;
        }

        // parse_url() does not accept null ports (random port assignment) => manually remove
        if (\substr($uri, -2) === ':0') {
            $parts = \parse_url(\substr($uri, 0, -2));
            if ($parts) {
                $parts['port'] = 0;
            }
        } else {
            $parts = \parse_url($uri);
        }

        // ensure URI contains TCP scheme, host and port
        if (!$parts || !isset($parts['scheme'], $parts['host'], $parts['port']) || $parts['scheme'] !== 'tcp') {
            throw new \InvalidArgumentException(
                'Invalid URI "' . $uri . '" given (EINVAL)',
                \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : (\defined('PCNTL_EINVAL') ? \PCNTL_EINVAL : 22)
            );
        }

        if (@\inet_pton(\trim($parts['host'], '[]')) === false) {
            throw new \InvalidArgumentException(
                'Given URI "' . $uri . '" does not contain a valid host IP (EINVAL)',
                \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : (\defined('PCNTL_EINVAL') ? \PCNTL_EINVAL : 22)
            );
        }

        $this->master = @\stream_socket_server(
            $uri,
            $errno,
            $errstr,
            \STREAM_SERVER_BIND | \STREAM_SERVER_LISTEN,
            \stream_context_create(array('socket' => $context + array('backlog' => 511)))
        );
        if (false === $this->master) {
            if ($errno === 0) {
                // PHP does not seem to report errno, so match errno from errstr
                // @link https://3v4l.org/3qOBl
                $errno = SocketServer::errno($errstr);
            }

            throw new \RuntimeException(
                'Failed to listen on "' . $uri . '": ' . $errstr . SocketServer::errconst($errno),
                $errno
            );
        }
        \stream_set_blocking($this->master, false);

        $this->resume();
    }

    public function getAddress()
    {
        if (!\is_resource($this->master)) {
            return null;
        }

        $address = \stream_socket_get_name($this->master, false);

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
        $this->emit('connection', array(
            new Connection($socket, $this->loop)
        ));
    }
}
