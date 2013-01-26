<?php

class PHPUnit_Util_Filters_TestFilterIterator extends RecursiveFilterIterator
{

    protected $filter = NULL;

    public function __construct($iterator, $filter) {
        parent::__construct($iterator);
        $this->filter = $filter;
    }

    public function accept() {
        $test = $this->getInnerIterator()->current();
        if ($test instanceof PHPUnit_Framework_TestSuite) {
            return true;
        }

        $tmp = PHPUnit_Util_Test::describe($test, FALSE);

        if ($tmp[0] != '') {
            $name = join('::', $tmp);
        } else {
            $name = $tmp[1];
        }

        return preg_match($this->filter, $name);
    }

}