<?php

namespace React\Socket;

use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise;
use BadMethodCallException;
use InvalidArgumentException;
use UnexpectedValueException;

final class SecureConnector implements ConnectorInterface
{
    private $connector;
    private $streamEncryption;
    private $context;

    /**
     * @param ConnectorInterface $connector
     * @param ?LoopInterface $loop
     * @param array $context
     */
    public function __construct(ConnectorInterface $connector, $loop = null, array $context = array())
    {
        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #2 ($loop) expected null|React\EventLoop\LoopInterface');
        }

        $this->connector = $connector;
        $this->streamEncryption = new StreamEncryption($loop ?: Loop::get(), false);
        $this->context = $context;
    }

    public function connect($uri)
    {
        if (!\function_exists('stream_socket_enable_crypto')) {
            return Promise\reject(new \BadMethodCallException('Encryption not supported on your platform (HHVM < 3.8?)')); // @codeCoverageIgnore
        }

        if (\strpos($uri, '://') === false) {
            $uri = 'tls://' . $uri;
        }

        $parts = \parse_url($uri);
        if (!$parts || !isset($parts['scheme']) || $parts['scheme'] !== 'tls') {
            return Promise\reject(new \InvalidArgumentException(
                'Given URI "' . $uri . '" is invalid (EINVAL)',
                \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : (\defined('PCNTL_EINVAL') ? \PCNTL_EINVAL : 22)
            ));
        }

        $context = $this->context;
        $encryption = $this->streamEncryption;
        $connected = false;
        /** @var \React\Promise\PromiseInterface<ConnectionInterface> $promise */
        $promise = $this->connector->connect(
            \str_replace('tls://', '', $uri)
        )->then(function (ConnectionInterface $connection) use ($context, $encryption, $uri, &$promise, &$connected) {
            // (unencrypted) TCP/IP connection succeeded
            $connected = true;

            if (!$connection instanceof Connection) {
                $connection->close();
                throw new \UnexpectedValueException('Base connector does not use internal Connection class exposing stream resource');
            }

            // set required SSL/TLS context options
            foreach ($context as $name => $value) {
                \stream_context_set_option($connection->stream, 'ssl', $name, $value);
            }

            // try to enable encryption
            return $promise = $encryption->enable($connection)->then(null, function ($error) use ($connection, $uri) {
                // establishing encryption failed => close invalid connection and return error
                $connection->close();

                throw new \RuntimeException(
                    'Connection to ' . $uri . ' failed during TLS handshake: ' . $error->getMessage(),
                    $error->getCode()
                );
            });
        }, function (\Exception $e) use ($uri) {
            if ($e instanceof \RuntimeException) {
                $message = \preg_replace('/^Connection to [^ ]+/', '', $e->getMessage());
                $e = new \RuntimeException(
                    'Connection to ' . $uri . $message,
                    $e->getCode(),
                    $e
                );

                // avoid garbage references by replacing all closures in call stack.
                // what a lovely piece of code!
                $r = new \ReflectionProperty('Exception', 'trace');
                if (\PHP_VERSION_ID < 80100) {
                    $r->setAccessible(true);
                }
                $trace = $r->getValue($e);

                // Exception trace arguments are not available on some PHP 7.4 installs
                // @codeCoverageIgnoreStart
                foreach ($trace as $ti => $one) {
                    if (isset($one['args'])) {
                        foreach ($one['args'] as $ai => $arg) {
                            if ($arg instanceof \Closure) {
                                $trace[$ti]['args'][$ai] = 'Object(' . \get_class($arg) . ')';
                            }
                        }
                    }
                }
                // @codeCoverageIgnoreEnd
                $r->setValue($e, $trace);
            }

            throw $e;
        });

        return new \React\Promise\Promise(
            function ($resolve, $reject) use ($promise) {
                $promise->then($resolve, $reject);
            },
            function ($_, $reject) use (&$promise, $uri, &$connected) {
                if ($connected) {
                    $reject(new \RuntimeException(
                        'Connection to ' . $uri . ' cancelled during TLS handshake (ECONNABORTED)',
                        \defined('SOCKET_ECONNABORTED') ? \SOCKET_ECONNABORTED : 103
                    ));
                }

                $promise->cancel();
                $promise = null;
            }
        );
    }
}
