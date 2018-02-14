<?php

class UndefinedIndex
{
    public function hello(array $a)
    {
        try {
            $this->world($a);
        } catch (\Throwable $e) {
            return 0;
        }

        return 1;
    }

    public function world(array $a)
    {
        return $a['index'];
    }
}
