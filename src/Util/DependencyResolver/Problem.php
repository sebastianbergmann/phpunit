<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\DependencyResolver;

class Problem extends \SplDoublyLinkedList
{
    /** @var object */
    protected $object;

    /** @var string */
    protected $name;

    /**
     * @param string $name
     * @param null $object
     * @param array $dependencies
     */
    public function __construct($name, $object, array $dependencies = [])
    {
        $this->name = $name;
        $this->object = $object;

        foreach ($dependencies as $dependency) {
            $this->push($dependency);
        }
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
