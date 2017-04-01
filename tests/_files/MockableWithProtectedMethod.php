<?php
class MockableWithProtectedMethod
{
    public function foo()
    {
        return $this->bar();
    }

    protected function bar()
    {
        return false;
    }
}
