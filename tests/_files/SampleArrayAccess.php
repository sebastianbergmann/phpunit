<?php
/**
 * Sample class that implements ArrayAccess copied from
 * http://www.php.net/manual/en/class.arrayaccess.php
 * with some minor changes
 * This class required for \PHPUnit\Framework\Constraint\ArrayHasKey testing
 */
class SampleArrayAccess implements ArrayAccess
{
    private $container;

    public function __construct()
    {
        $this->container = [];
    }
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }
}
