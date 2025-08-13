--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5991
--SKIPIF--
<?php
if (rand(0,1)) // intentional PHP Parse error (missing opening curly brace)
}
--FILE--
<?php declare(strict_types=1);
echo 'hello world';
--EXPECTF--
hello world
