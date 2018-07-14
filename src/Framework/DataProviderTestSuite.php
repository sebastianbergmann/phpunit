<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use ReflectionClass;

class DataProviderTestSuite extends DataProvidedTestSuite
{
    private $provider;
    public function __construct($provider, ReflectionClass $theClass, string $method)
    {
        parent::__construct($theClass, $method);
        $this->provider = $provider;
    }

    protected function yieldData(): iterable
    {
        if (!$this->theClass->hasMethod($this->provider)) {
            throw new Exception();
        }
        if (!$this->theClass->getMethod($this->provider)->isPublic()) {
            throw new Exception();
        }
        if ($this->theClass->getMethod($this->provider)->isAbstract()) {
            throw new Exception();
        }
        yield from $this->theClass->newInstanceArgs()->{$this->provider}();
    }
}
