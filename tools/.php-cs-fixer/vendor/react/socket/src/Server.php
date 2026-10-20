<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Exception;

/**
 * @deprecated 1.9.0 See `SocketServer` instead
 * @see SocketServer
 */
final class Server extends EventEmitter implements ServerInterface
{
    private $server;

    /**
     * [Deprecated] `Server`
     *
     * This class exists for BC reasons only and should not be used anymore.
     *
     * ```php
     * // deprecated
     * $socket = new React\Socket\Server(0);
     * $socket = new React\Socket\Server('127.0.0.1:8000');
     * $socket = new React\Socket\Server('127.0.0.1:8000', null, $context);
     * $socket = new React\Socket\Server('127.0.0.1:8000', $loop, $context);
     *
     * // new
     * $socket = new React\Socket\SocketServer('127.0.0.1:0');
     * $socket = new React\Socket\SocketServer('127.0.0.1:8000');
     * $socket = new React\Socket\SocketServer('127.0.0.1:8000', $context);
     * $socket = new React\Socket\SocketServer('127.0.0.1:8000', $context, $loop);
     * ```
     *
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * For BC reasons, you can also pass the TCP socket context options as a simple
     * array without wrapping this in another array under the `tcp` key.
     *
     * @param string|int     $uri
     * @param ?LoopInterface $loop
     * @param array          $context
     * @deprecated 1.9.0 See `SocketServer` instead
     * @see SocketServer
     */
    public function __construct($uri, $loop = null, array $context = array())
    {
        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #2 ($loop) expected null|React\EventLoop\LoopInterface');
        }

        $loop = $loop ?: Loop::get();

        // sanitize TCP context options if not properly wrapped
        if ($context && (!isset($context['tcp']) && !isset($context['tls']) && !isset($context['unix']))) {
            $context = array('tcp' => $context);
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
        } else {
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
        $server->on('error', function (Exception $error) use ($that) {
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
}
