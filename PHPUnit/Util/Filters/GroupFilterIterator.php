<?php

abstract class PHPUnit_Util_Filters_GroupFilterIterator extends RecursiveFilterIterator
{

    /**
     * @var array
     */
    protected $groupTests = array();

    public function __construct($iterator, $groups, PHPUnit_Framework_TestSuite $parentSuite) {
        parent::__construct($iterator);

        foreach ($parentSuite->getGroupDetails() as $group => $tests) {
            if (in_array($group, $groups)) {
                $testHashes = array_map(function($test) {
                    return spl_object_hash($test);
                }, $tests);
                $this->groupTests += $testHashes;
            }
        }
    }

}