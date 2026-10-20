<?php

namespace React\Socket;

use React\Dns\Model\Message;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise;
use React\Promise\PromiseInterface;

/**
 * @internal
 */
final class HappyEyeBallsConnectionBuilder
{
    /**
     * As long as we haven't connected yet keep popping an IP address of the connect queue until one of them
     * succeeds or they all fail. We will wait 100ms between connection attempts as per RFC.
     *
     * @link https://tools.ietf.org/html/rfc8305#section-5
     */
    const CONNECTION_ATTEMPT_DELAY = 0.1;

    /**
     * Delay `A` lookup by 50ms sending out connection to IPv4 addresses when IPv6 records haven't
     * resolved yet as per RFC.
     *
     * @link https://tools.ietf.org/html/rfc8305#section-3
     */
    const RESOLUTION_DELAY = 0.05;

    public $loop;
    public $connector;
    public $resolver;
    public $uri;
    public $host;
    public $resolved = array(
        Message::TYPE_A    => false,
        Message::TYPE_AAAA => false,
    );
    public $resolverPromises = array();
    public $connectionPromises = array();
    public $connectQueue = array();
    public $nextAttemptTimer;
    public $parts;
    public $ipsCount = 0;
    public $failureCount = 0;
    public $resolve;
    public $reject;

    public $lastErrorFamily;
    public $lastError6;
    public $lastError4;

    public function __construct(LoopInterface $loop, ConnectorInterface $connector, ResolverInterface $resolver, $uri, $host, $parts)
    {
        $this->loop = $loop;
        $this->connector = $connector;
        $this->resolver = $resolver;
        $this->uri = $uri;
        $this->host = $host;
        $this->parts = $parts;
    }

    public function connect()
    {
        $that = $this;
        return new Promise\Promise(function ($resolve, $reject) use ($that) {
            $lookupResolve = function ($type) use ($that, $resolve, $reject) {
                return function (array $ips) use ($that, $type, $resolve, $reject) {
                    unset($that->resolverPromises[$type]);
                    $that->resolved[$type] = true;

                    $that->mixIpsIntoConnectQueue($ips);

                    // start next connection attempt if not already awaiting next
                    if ($that->nextAttemptTimer === null && $that->connectQueue) {
                        $that->check($resolve, $reject);
                    }
                };
            };

            $that->resolverPromises[Message::TYPE_AAAA] = $that->resolve(Message::TYPE_AAAA, $reject)->then($lookupResolve(Message::TYPE_AAAA));
            $that->resolverPromises[Message::TYPE_A] = $that->resolve(Message::TYPE_A, $reject)->then(function (array $ips) use ($that) {
                // happy path: IPv6 has resolved already (or could not resolve), continue with IPv4 addresses
                if ($that->resolved[Message::TYPE_AAAA] === true || !$ips) {
                    return $ips;
                }

                // Otherwise delay processing IPv4 lookup until short timer passes or IPv6 resolves in the meantime
                $deferred = new Promise\Deferred(function () use (&$ips) {
                    // discard all IPv4 addresses if cancelled
                    $ips = array();
                });
                $timer = $that->loop->addTimer($that::RESOLUTION_DELAY, function () use ($deferred, $ips) {
                    $deferred->resolve($ips);
                });

                $that->resolverPromises[Message::TYPE_AAAA]->then(function () use ($that, $timer, $deferred, &$ips) {
                    $that->loop->cancelTimer($timer);
                    $deferred->resolve($ips);
                });

                return $deferred->promise();
            })->then($lookupResolve(Message::TYPE_A));
        }, function ($_, $reject) use ($that) {
            $reject(new \RuntimeException(
                'Connection to ' . $that->uri . ' cancelled' . (!$that->connectionPromises ? ' during DNS lookup' : '') . ' (ECONNABORTED)',
                \defined('SOCKET_ECONNABORTED') ? \SOCKET_ECONNABORTED : 103
            ));
            $_ = $reject = null;

            $that->cleanUp();
        });
    }

    /**
     * @internal
     * @param int      $type   DNS query type
     * @param callable $reject
     * @return \React\Promise\PromiseInterface<string[]> Returns a promise that
     *     always resolves with a list of IP addresses on success or an empty
     *     list on error.
     */
    public function resolve($type, $reject)
    {
        $that = $this;
        return $that->resolver->resolveAll($that->host, $type)->then(null, function (\Exception $e) use ($type, $reject, $that) {
            unset($that->resolverPromises[$type]);
            $that->resolved[$type] = true;

            if ($type === Message::TYPE_A) {
                $that->lastError4 = $e->getMessage();
                $that->lastErrorFamily = 4;
            } else {
                $that->lastError6 = $e->getMessage();
                $that->lastErrorFamily = 6;
            }

            // cancel next attempt timer when there are no more IPs to connect to anymore
            if ($that->nextAttemptTimer !== null && !$that->connectQueue) {
                $that->loop->cancelTimer($that->nextAttemptTimer);
                $that->nextAttemptTimer = null;
            }

            if ($that->hasBeenResolved() && $that->ipsCount === 0) {
                $reject(new \RuntimeException(
                    $that->error(),
                    0,
                    $e
                ));
            }

            // Exception already handled above, so don't throw an unhandled rejection here
            return array();
        });
    }

    /**
     * @internal
     */
    public function check($resolve, $reject)
    {
        $ip = \array_shift($this->connectQueue);

        // start connection attempt and remember array position to later unset again
        $this->connectionPromises[] = $this->attemptConnection($ip);
        \end($this->connectionPromises);
        $index = \key($this->connectionPromises);

        $that = $this;
        $that->connectionPromises[$index]->then(function ($connection) use ($that, $index, $resolve) {
            unset($that->connectionPromises[$index]);

            $that->cleanUp();

            $resolve($connection);
        }, function (\Exception $e) use ($that, $index, $ip, $resolve, $reject) {
            unset($that->connectionPromises[$index]);

            $that->failureCount++;

            $message = \preg_replace('/^(Connection to [^ ]+)[&?]hostname=[^ &]+/', '$1', $e->getMessage());
            if (\strpos($ip, ':') === false) {
                $that->lastError4 = $message;
                $that->lastErrorFamily = 4;
            } else {
                $that->lastError6 = $message;
                $that->lastErrorFamily = 6;
            }

            // start next connection attempt immediately on error
            if ($that->connectQueue) {
                if ($that->nextAttemptTimer !== null) {
                    $that->loop->cancelTimer($that->nextAttemptTimer);
                    $that->nextAttemptTimer = null;
                }

                $that->check($resolve, $reject);
            }

            if ($that->hasBeenResolved() === false) {
                return;
            }

            if ($that->ipsCount === $that->failureCount) {
                $that->cleanUp();

                $reject(new \RuntimeException(
                    $that->error(),
                    $e->getCode(),
                    $e
                ));
            }
        });

        // Allow next connection attempt in 100ms: https://tools.ietf.org/html/rfc8305#section-5
        // Only start timer when more IPs are queued or when DNS query is still pending (might add more IPs)
        if ($this->nextAttemptTimer === null && (\count($this->connectQueue) > 0 || $this->resolved[Message::TYPE_A] === false || $this->resolved[Message::TYPE_AAAA] === false)) {
            $this->nextAttemptTimer = $this->loop->addTimer(self::CONNECTION_ATTEMPT_DELAY, function () use ($that, $resolve, $reject) {
                $that->nextAttemptTimer = null;

                if ($that->connectQueue) {
                    $that->check($resolve, $reject);
                }
            });
        }
    }

    /**
     * @internal
     */
    public function attemptConnection($ip)
    {
        $uri = Connector::uri($this->parts, $this->host, $ip);

        return $this->connector->connect($uri);
    }

    /**
     * @internal
     */
    public function cleanUp()
    {
        // clear list of outstanding IPs to avoid creating new connections
        $this->connectQueue = array();

        // cancel pending connection attempts
        foreach ($this->connectionPromises as $connectionPromise) {
            if ($connectionPromise instanceof PromiseInterface && \method_exists($connectionPromise, 'cancel')) {
                $connectionPromise->cancel();
            }
        }

        // cancel pending DNS resolution (cancel IPv4 first in case it is awaiting IPv6 resolution delay)
        foreach (\array_reverse($this->resolverPromises) as $resolverPromise) {
            if ($resolverPromise instanceof PromiseInterface && \method_exists($resolverPromise, 'cancel')) {
                $resolverPromise->cancel();
            }
        }

        if ($this->nextAttemptTimer instanceof TimerInterface) {
            $this->loop->cancelTimer($this->nextAttemptTimer);
            $this->nextAttemptTimer = null;
        }
    }

    /**
     * @internal
     */
    public function hasBeenResolved()
    {
        foreach ($this->resolved as $typeHasBeenResolved) {
            if ($typeHasBeenResolved === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mixes an array of IP addresses into the connect queue in such a way they alternate when attempting to connect.
     * The goal behind it is first attempt to connect to IPv6, then to IPv4, then to IPv6 again until one of those
     * attempts succeeds.
     *
     * @link https://tools.ietf.org/html/rfc8305#section-4
     *
     * @internal
     */
    public function mixIpsIntoConnectQueue(array $ips)
    {
        \shuffle($ips);
        $this->ipsCount += \count($ips);
        $connectQueueStash = $this->connectQueue;
        $this->connectQueue = array();
        while (\count($connectQueueStash) > 0 || \count($ips) > 0) {
            if (\count($ips) > 0) {
                $this->connectQueue[] = \array_shift($ips);
            }
            if (\count($connectQueueStash) > 0) {
                $this->connectQueue[] = \array_shift($connectQueueStash);
            }
        }
    }

    /**
     * @internal
     * @return string
     */
    public function error()
    {
        if ($this->lastError4 === $this->lastError6) {
            $message = $this->lastError6;
        } elseif ($this->lastErrorFamily === 6) {
            $message = 'Last error for IPv6: ' . $this->lastError6 . '. Previous error for IPv4: ' . $this->lastError4;
        } else {
            $message = 'Last error for IPv4: ' . $this->lastError4 . '. Previous error for IPv6: ' . $this->lastError6;
        }

        if ($this->hasBeenResolved() && $this->ipsCount === 0) {
            if ($this->lastError6 === $this->lastError4) {
                $message = ' during DNS lookup: ' . $this->lastError6;
            } else {
                $message = ' during DNS lookup. ' . $message;
            }
        } else {
            $message = ': ' . $message;
        }

        return 'Connection to ' . $this->uri . ' failed'  . $message;
    }
}
