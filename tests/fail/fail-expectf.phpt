--TEST--
// This test intentionally fails and it is checked by Travis.
--FILE--
Foo
Multiline diff
Buzz
--EXPECTF--
%s
Multiline
%s
