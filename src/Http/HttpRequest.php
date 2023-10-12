<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Marco De Felice <marco.defelice@mlabfactory.it
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Testing\TestResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HttpRequest extends AssertHttpResponse {

    private array $headers = [];
    private ResponseInterface $response;
    private int $statusCode;
    private CookieJar $cookies;

    private string $domain;
    private array $options; //Guzzle HTTP Request options https://docs.guzzlephp.org/en/stable/quickstart.html#making-a-request

    public function __construct($domain, $options = [])
    {
        $this->setCookies([]);
        $this->domain = $domain;
        $this->options = $options;
    }

    /**
     *  invoke a request
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $cookies
     * 
     * @return TestResponse
     */
    private function invoke(string $method, string $uri, array $data = []): TestResponse
    {
         $jar = $this->cookies;

        try {
            $client = new Client(
                $this->options
            );

            $this->response = $client->request($method, $this->domain.$uri, array_merge($data,[
                'cookies' => $jar
            ]));

            $this->statusCode = $this->response->getStatusCode();
            $this->headers = $this->response->getHeaders();

            return new TestResponse($this);
            
        } catch (\Throwable $e) {

            $this->statusCode = $e->getCode();
            return new TestResponse($this);
        }
    }

    /**
     * send GET request
     * @param string $uri
     * 
     * @return TestResponse
     */
    public function get(string $uri): TestResponse
    {
        return $this->invoke("GET", $this->domain.$uri);
    }

    /**
     * send POST request
     * @param string $uri
     * @param array $data
     * 
     * @return TestResponse
     */
    public function post(string $uri, array $data, array $headers): TestResponse
    {
        return $this->invoke("POST", $this->domain.$uri, [
            'body' => $data,
            'headers' => $headers
        ]);
    }

    /**
     * send PUT request
     * @param string $uri
     * @param array $data to send
     * 
     * @return TestResponse
     */
    public function put(string $uri, array $data, array $headers): TestResponse
    {
        return $this->invoke("PUT", $this->domain.$uri, [
            'body' => $data,
            'headers' => $headers
        ]);
    }

    /**
     * send DELETE request
     * @param string $uri
     * 
     * @return TestResponse
     */
    public function delete(string $uri, array $headers): TestResponse
    {
        return $this->invoke("DELETE", $this->domain.$uri, [
            'headers' => $headers
        ]);
    }

    /**
     * send OPTION request
     * @param string $uri
     * 
     * @return TestResponse
     */
    public function option(string $uri, array $headers): TestResponse
    {
        return $this->invoke("OPTION", $this->domain.$uri, [
            'headers' => $headers
        ]);
    }

    /**
     * send PATCH request
     * @param string $uri
     * 
     * @return TestResponse
     */
    public function patch(string $uri, array $headers): TestResponse
    {
        return $this->invoke("PATCH", $this->domain.$uri, [
            'headers' => $headers
        ]);
    }

    /**
     * Get the value of statusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the value of content
     *
     * @return string
     */
    public function getContent(): StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     *  GuzzleHttp\Promise\PromiseInterface that is fulfilled with a
     *  Psr7\Http\Message\ResponseInterface on success.
     */
    public function isSuccessful(): bool
    {
        if(!$this->response instanceof ResponseInterface) {
            return false;
        }

        return true;
    }

    /**
     * Get the value of headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get the value of headers
     *
     * @return array
     */
    public function getStatusCodeRedirect(): string
    {
        return $this->headers[\GuzzleHttp\RedirectMiddleware::STATUS_HISTORY_HEADER];
    }

    /**
     * Get the value of cookies
     *
     * @return array
     */
    public function getCookies(string $name): string
    {
        return $this->cookies->getCookieByName($name)->__toString();
    }

    /**
     * Set the value of cookies
     *
     * @param array $cookies
     *
     * @return self
     */
    public function setCookies(array $cookies): self
    {
        if(empty($this->domain)) {
            throw new Exception("APP_DOMAIN env variable must be valid to running http tests");
        }

        $this->cookies = CookieJar::fromArray(
            $cookies, $this->domain
        );

        return $this;
    }
}