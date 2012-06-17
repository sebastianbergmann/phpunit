<?php

class PHPUnit_Util_Filters_IncludeGroupFilterIterator extends PHPUnit_Util_Filters_GroupFilterIterator
{

    public function accept() {
        $test = $this->getInnerIterator()->current();
        if ($test instanceof PHPUnit_Framework_TestSuite) {
            return true;
        }
        return in_array(spl_object_hash($test), $this->groupTests);
    }

}