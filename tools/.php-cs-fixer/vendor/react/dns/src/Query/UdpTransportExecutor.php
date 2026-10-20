<?php

namespace React\Dns\Query;

use React\Dns\Model\Message;
use React\Dns\Protocol\BinaryDumper;
use React\Dns\Protocol\Parser;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;

/**
 * Send DNS queries over a UDP transport.
 *
 * This is the main class that sends a DNS query to your DNS server and is used
 * internally by the `Resolver` for the actual message transport.
 *
 * For more advanced usages one can utilize this class directly.
 * The following example looks up the `IPv6` address for `igor.io`.
 *
 * ```php
 * $executor = new UdpTransportExecutor('8.8.8.8:53');
 *
 * $executor->query(
 *     new Query($name, Message::TYPE_AAAA, Message::CLASS_IN)
 * )->then(function (Message $message) {
 *     foreach ($message->answers as $answer) {
 *         echo 'IPv6: ' . $answer->data . PHP_EOL;
 *     }
 * }, 'printf');
 * ```
 *
 * See also the [fourth example](examples).
 *
 * Note that this executor does not implement a timeout, so you will very likely
 * want to use this in combination with a `TimeoutExecutor` like this:
 *
 * ```php
 * $executor = new TimeoutExecutor(
 *     new UdpTransportExecutor($nameserver),
 *     3.0
 * );
 * ```
 *
 * Also note that this executor uses an unreliable UDP transport and that it
 * does not implement any retry logic, so you will likely want to use this in
 * combination with a `RetryExecutor` like this:
 *
 * ```php
 * $executor = new RetryExecutor(
 *     new TimeoutExecutor(
 *         new UdpTransportExecutor($nameserver),
 *         3.0
 *     )
 * );
 * ```
 *
 * Note that this executor is entirely async and as such allows you to execute
 * any number of queries concurrently. You should probably limit the number of
 * concurrent queries in your application or you're very likely going to face
 * rate limitations and bans on the resolver end. For many common applications,
 * you may want to avoid sending the same query multiple times when the first
 * one is still pending, so you will likely want to use this in combination with
 * a `CoopExecutor` like this:
 *
 * ```php
 * $executor = new CoopExecutor(
 *     new RetryExecutor(
 *         new TimeoutExecutor(
 *             new UdpTransportExecutor($nameserver),
 *             3.0
 *         )
 *     )
 * );
 * ```
 *
 * > Internally, this class uses PHP's UDP sockets and does not take advantage
 *   of [react/datagram](https://github.com/reactphp/datagram) purely for
 *   organizational reasons to avoid a cyclic dependency between the two
 *   packages. Higher-level components should take advantage of the Datagram
 *   component instead of reimplementing this socket logic from scratch.
 */
final class UdpTransportExecutor implements ExecutorInterface
{
    private $nameserver;
    private $loop;
    private $parser;
    private $dumper;

    /**
     * maximum UDP packet size to send and receive
     *
     * @var int
     */
    private $maxPacketSize = 512;

    /**
     * @param string         $nameserver
     * @param ?LoopInterface $loop
     */
    public function __construct($nameserver, $loop = null)
    {
        if (\strpos($nameserver, '[') === false && \substr_count($nameserver, ':') >= 2 && \strpos($nameserver, '://') === false) {
            // several colons, but not enclosed in square brackets => enclose IPv6 address in square brackets
            $nameserver = '[' . $nameserver . ']';
        }

        $parts = \parse_url((\strpos($nameserver, '://') === false ? 'udp://' : '') . $nameserver);
        if (!isset($parts['scheme'], $parts['host']) || $parts['scheme'] !== 'udp' || @\inet_pton(\trim($parts['host'], '[]')) === false) {
            throw new \InvalidArgumentException('Invalid nameserver address given');
        }

        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #2 ($loop) expected null|React\EventLoop\LoopInterface');
        }

        $this->nameserver = 'udp://' . $parts['host'] . ':' . (isset($parts['port']) ? $parts['port'] : 53);
        $this->loop = $loop ?: Loop::get();
        $this->parser = new Parser();
        $this->dumper = new BinaryDumper();
    }

    public function query(Query $query)
    {
        $request = Message::createRequestForQuery($query);

        $queryData = $this->dumper->toBinary($request);
        if (isset($queryData[$this->maxPacketSize])) {
            return \React\Promise\reject(new \RuntimeException(
                'DNS query for ' . $query->describe() . ' failed: Query too large for UDP transport',
                \defined('SOCKET_EMSGSIZE') ? \SOCKET_EMSGSIZE : 90
            ));
        }

        // UDP connections are instant, so try connection without a loop or timeout
        $errno = 0;
        $errstr = '';
        $socket = @\stream_socket_client($this->nameserver, $errno, $errstr, 0);
        if ($socket === false) {
            return \React\Promise\reject(new \RuntimeException(
                'DNS query for ' . $query->describe() . ' failed: Unable to connect to DNS server ' . $this->nameserver . ' ('  . $errstr . ')',
                $errno
            ));
        }

        // set socket to non-blocking and immediately try to send (fill write buffer)
        \stream_set_blocking($socket, false);

        \set_error_handler(function ($_, $error) use (&$errno, &$errstr) {
            // Write may potentially fail, but most common errors are already caught by connection check above.
            // Among others, macOS is known to report here when trying to send to broadcast address.
            // This can also be reproduced by writing data exceeding `stream_set_chunk_size()` to a server refusing UDP data.
            // fwrite(): send of 8192 bytes failed with errno=111 Connection refused
            \preg_match('/errno=(\d+) (.+)/', $error, $m);
            $errno = isset($m[1]) ? (int) $m[1] : 0;
            $errstr = isset($m[2]) ? $m[2] : $error;
        });

        $written = \fwrite($socket, $queryData);

        \restore_error_handler();

        if ($written !== \strlen($queryData)) {
            return \React\Promise\reject(new \RuntimeException(
                'DNS query for ' . $query->describe() . ' failed: Unable to send query to DNS server ' . $this->nameserver . ' ('  . $errstr . ')',
                $errno
            ));
        }

        $loop = $this->loop;
        $deferred = new Deferred(function () use ($loop, $socket, $query) {
            // cancellation should remove socket from loop and close socket
            $loop->removeReadStream($socket);
            \fclose($socket);

            throw new CancellationException('DNS query for ' . $query->describe() . ' has been cancelled');
        });

        $max = $this->maxPacketSize;
        $parser = $this->parser;
        $nameserver = $this->nameserver;
        $loop->addReadStream($socket, function ($socket) use ($loop, $deferred, $query, $parser, $request, $max, $nameserver) {
            // try to read a single data packet from the DNS server
            // ignoring any errors, this is uses UDP packets and not a stream of data
            $data = @\fread($socket, $max);
            if ($data === false) {
                return;
            }

            try {
                $response = $parser->parseMessage($data);
            } catch (\Exception $e) {
                // ignore and await next if we received an invalid message from remote server
                // this may as well be a fake response from an attacker (possible DOS)
                return;
            }

            // ignore and await next if we received an unexpected response ID
            // this may as well be a fake response from an attacker (possible cache poisoning)
            if ($response->id !== $request->id) {
                return;
            }

            // we only react to the first valid message, so remove socket from loop and close
            $loop->removeReadStream($socket);
            \fclose($socket);

            if ($response->tc) {
                $deferred->reject(new \RuntimeException(
                    'DNS query for ' . $query->describe() . ' failed: The DNS server ' . $nameserver . ' returned a truncated result for a UDP query',
                    \defined('SOCKET_EMSGSIZE') ? \SOCKET_EMSGSIZE : 90
                ));
                return;
            }

            $deferred->resolve($response);
        });

        return $deferred->promise();
    }
}
