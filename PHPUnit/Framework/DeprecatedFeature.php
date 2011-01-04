<?php

class PHPUnit_Framework_DeprecatedFeature
{
    
    protected $traceInfo = array();
    protected $message = null;
    
    public function __construct($message, Array $traceInfo = null)
    {
        $this->message = $message;
        if ($traceInfo) {
            $this->traceInfo = $traceInfo;
        }
    }
    
    public function __toString()
    {
        $string = '';
        if (isset($this->traceInfo['file'])) {
            $string .= $this->traceInfo['file'];
            if (isset($this->traceInfo['line'])) {
                $string .= ':' . $this->traceInfo['line'] . ' - ';
            }
        }
        $string .= $this->message;
        return $string;
    }
    
}
