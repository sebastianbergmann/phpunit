<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * The `UnixServer` class implements the `ServerInterface` and
 * is responsible for accepting plaintext connections on unix domain sockets.
 *
 * ```php
 * $server = new React\Socket\UnixServer('unix:///tmp/app.sock');
 * ```
 *
 * See also the `ServerInterface` for more details.
 *
 * @see ServerInterface
 * @see ConnectionInterface
 */
final class UnixServer extends EventEmitter implements ServerInterface
{
    private $master;
    private $loop;
    private $listening = false;

    /**
     * Creates a plaintext socket server and starts listening on the given unix socket
     *
     * This starts accepting new incoming connections on the given address.
     * See also the `connection event` documented in the `ServerInterface`
     * for more details.
     *
     * ```php
     * $server = new React\Socket\UnixServer('unix:///tmp/app.sock');
     * ```
     *
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * @param string         $path
     * @param ?LoopInterface $loop
     * @param array          $context
     * @throws InvalidArgumentException if the listening address is invalid
     * @throws RuntimeException if listening on this address fails (already in use etc.)
     */
    public function __construct($path, $loop = null, array $context = array())
    {
        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #2 ($loop) expected null|React\EventLoop\LoopInterface');
        }

        $this->loop = $loop ?: Loop::get();

        if (\strpos($path, '://') === false) {
            $path = 'unix://' . $path;
        } elseif (\substr($path, 0, 7) !== 'unix://') {
            throw new \InvalidArgumentException(
                'Given URI "' . $path . '" is invalid (EINVAL)',
                \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : (\defined('PCNTL_EINVAL') ? \PCNTL_EINVAL : 22)
            );
        }

        $errno = 0;
        $errstr = '';
        \set_error_handler(function ($_, $error) use (&$errno, &$errstr) {
            // PHP does not seem to report errno/errstr for Unix domain sockets (UDS) right now.
            // This only applies to UDS server sockets, see also https://3v4l.org/NAhpr.
            // Parse PHP warning message containing unknown error, HHVM reports proper info at least.
            if (\preg_match('/\(([^\)]+)\)|\[(\d+)\]: (.*)/', $error, $match)) {
                $errstr = isset($match[3]) ? $match['3'] : $match[1];
                $errno = isset($match[2]) ? (int)$match[2] : 0;
            }
        });

        $this->master = \stream_socket_server(
            $path,
            $errno,
            $errstr,
            \STREAM_SERVER_BIND | \STREAM_SERVER_LISTEN,
            \stream_context_create(array('socket' => $context))
        );

        \restore_error_handler();

        if (false === $this->master) {
            throw new \RuntimeException(
                'Failed to listen on Unix domain socket "' . $path . '": ' . $errstr . SocketServer::errconst($errno),
                $errno
            );
        }
        \stream_set_blocking($this->master, 0);

        $this->resume();
    }

    public function getAddress()
    {
        if (!\is_resource($this->master)) {
            return null;
        }

        return 'unix://' . \stream_socket_get_name($this->master, false);
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
        if ($this->listening || !is_resource($this->master)) {
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
        $connection->unix = true;

        $this->emit('connection', array(
            $connection
        ));
    }
}
