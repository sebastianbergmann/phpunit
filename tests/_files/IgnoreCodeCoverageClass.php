<?php
class IgnoreCodeCoverageClass
{
    /**
     * @codeCoverageIgnore
     */
    public function returnTrue()
    {
        return true;
    }
    
    public function returnFalse()
    {
        return false;
    }
}
