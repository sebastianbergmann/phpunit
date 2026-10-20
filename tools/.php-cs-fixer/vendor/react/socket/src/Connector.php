<?php

namespace React\Socket;

use React\Dns\Config\Config as DnsConfig;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\LoopInterface;

/**
 * The `Connector` class is the main class in this package that implements the
 * `ConnectorInterface` and allows you to create streaming connections.
 *
 * You can use this connector to create any kind of streaming connections, such
 * as plaintext TCP/IP, secure TLS or local Unix connection streams.
 *
 * Under the hood, the `Connector` is implemented as a *higher-level facade*
 * for the lower-level connectors implemented in this package. This means it
 * also shares all of their features and implementation details.
 * If you want to typehint in your higher-level protocol implementation, you SHOULD
 * use the generic [`ConnectorInterface`](#connectorinterface) instead.
 *
 * @see ConnectorInterface for the base interface
 */
final class Connector implements ConnectorInterface
{
    private $connectors = array();

    /**
     * Instantiate new `Connector`
     *
     * ```php
     * $connector = new React\Socket\Connector();
     * ```
     *
     * This class takes two optional arguments for more advanced usage:
     *
     * ```php
     * // constructor signature as of v1.9.0
     * $connector = new React\Socket\Connector(array $context = [], ?LoopInterface $loop = null);
     *
     * // legacy constructor signature before v1.9.0
     * $connector = new React\Socket\Connector(?LoopInterface $loop = null, array $context = []);
     * ```
     *
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * @param array|LoopInterface|null $context
     * @param null|LoopInterface|array $loop
     * @throws \InvalidArgumentException for invalid arguments
     */
    public function __construct($context = array(), $loop = null)
    {
        // swap arguments for legacy constructor signature
        if (($context instanceof LoopInterface || $context === null) && (\func_num_args() <= 1 || \is_array($loop))) {
            $swap = $loop === null ? array(): $loop;
            $loop = $context;
            $context = $swap;
        }

        if (!\is_array($context) || ($loop !== null && !$loop instanceof LoopInterface)) {
            throw new \InvalidArgumentException('Expected "array $context" and "?LoopInterface $loop" arguments');
        }

        // apply default options if not explicitly given
        $context += array(
            'tcp' => true,
            'tls' => true,
            'unix' => true,

            'dns' => true,
            'timeout' => true,
            'happy_eyeballs' => true,
        );

        if ($context['timeout'] === true) {
            $context['timeout'] = (float)\ini_get("default_socket_timeout");
        }

        if ($context['tcp'] instanceof ConnectorInterface) {
            $tcp = $context['tcp'];
        } else {
            $tcp = new TcpConnector(
                $loop,
                \is_array($context['tcp']) ? $context['tcp'] : array()
            );
        }

        if ($context['dns'] !== false) {
            if ($context['dns'] instanceof ResolverInterface) {
                $resolver = $context['dns'];
            } else {
                if ($context['dns'] !== true) {
                    $config = $context['dns'];
                } else {
                    // try to load nameservers from system config or default to Google's public DNS
                    $config = DnsConfig::loadSystemConfigBlocking();
                    if (!$config->nameservers) {
                        $config->nameservers[] = '8.8.8.8'; // @codeCoverageIgnore
                    }
                }

                $factory = new DnsFactory();
                $resolver = $factory->createCached(
                    $config,
                    $loop
                );
            }

            if ($context['happy_eyeballs'] === true) {
                $tcp = new HappyEyeBallsConnector($loop, $tcp, $resolver);
            } else {
                $tcp = new DnsConnector($tcp, $resolver);
            }
        }

        if ($context['tcp'] !== false) {
            $context['tcp'] = $tcp;

            if ($context['timeout'] !== false) {
                $context['tcp'] = new TimeoutConnector(
                    $context['tcp'],
                    $context['timeout'],
                    $loop
                );
            }

            $this->connectors['tcp'] = $context['tcp'];
        }

        if ($context['tls'] !== false) {
            if (!$context['tls'] instanceof ConnectorInterface) {
                $context['tls'] = new SecureConnector(
                    $tcp,
                    $loop,
                    \is_array($context['tls']) ? $context['tls'] : array()
                );
            }

            if ($context['timeout'] !== false) {
                $context['tls'] = new TimeoutConnector(
                    $context['tls'],
                    $context['timeout'],
                    $loop
                );
            }

            $this->connectors['tls'] = $context['tls'];
        }

        if ($context['unix'] !== false) {
            if (!$context['unix'] instanceof ConnectorInterface) {
                $context['unix'] = new UnixConnector($loop);
            }
            $this->connectors['unix'] = $context['unix'];
        }
    }

    public function connect($uri)
    {
        $scheme = 'tcp';
        if (\strpos($uri, '://') !== false) {
            $scheme = (string)\substr($uri, 0, \strpos($uri, '://'));
        }

        if (!isset($this->connectors[$scheme])) {
            return \React\Promise\reject(new \RuntimeException(
                'No connector available for URI scheme "' . $scheme . '" (EINVAL)',
                \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : (\defined('PCNTL_EINVAL') ? \PCNTL_EINVAL : 22)
            ));
        }

        return $this->connectors[$scheme]->connect($uri);
    }


    /**
     * [internal] Builds on URI from the given URI parts and ip address with original hostname as query
     *
     * @param array  $parts
     * @param string $host
     * @param string $ip
     * @return string
     * @internal
     */
    public static function uri(array $parts, $host, $ip)
    {
        $uri = '';

        // prepend original scheme if known
        if (isset($parts['scheme'])) {
            $uri .= $parts['scheme'] . '://';
        }

        if (\strpos($ip, ':') !== false) {
            // enclose IPv6 addresses in square brackets before appending port
            $uri .= '[' . $ip . ']';
        } else {
            $uri .= $ip;
        }

        // append original port if known
        if (isset($parts['port'])) {
            $uri .= ':' . $parts['port'];
        }

        // append orignal path if known
        if (isset($parts['path'])) {
            $uri .= $parts['path'];
        }

        // append original query if known
        if (isset($parts['query'])) {
            $uri .= '?' . $parts['query'];
        }

        // append original hostname as query if resolved via DNS and if
        // destination URI does not contain "hostname" query param already
        $args = array();
        \parse_str(isset($parts['query']) ? $parts['query'] : '', $args);
        if ($host !== $ip && !isset($args['hostname'])) {
            $uri .= (isset($parts['query']) ? '&' : '?') . 'hostname=' . \rawurlencode($host);
        }

        // append original fragment if known
        if (isset($parts['fragment'])) {
            $uri .= '#' . $parts['fragment'];
        }

        return $uri;
    }
}
