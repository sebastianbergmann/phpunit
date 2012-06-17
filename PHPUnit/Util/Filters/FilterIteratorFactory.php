<?php

class PHPUnit_Util_Filters_FilterIteratorFactory
{

    private $filters = array();

    public function addFilter(ReflectionClass $filter, $args) {
        if (!$filter->isSubclassOf('FilterIterator')) {
            throw new InvalidArgumentException(
                'Class "' . $filter->name . '" does not extend FilterIterator.'
            );
        }
        $this->filters[] = array($filter, $args);
    }

    /**
     * @return FilterIterator
     */
    public function factory(Iterator $iterator, PHPUnit_Framework_TestSuite $parent) {
        foreach ($this->filters as $filter) {
            list($class, $args) = $filter;
            /* @var $class ReflectionClass */
            $iterator = $class->newInstance($iterator, $args, $parent);
        }

        return $iterator;
    }

}