--TEST--
INI extension array
--INI--
extension=doesnotexist1.so
extension=doesnotexist2.so
--FILE--
<?php echo 'ok';
--EXPECTF--
%Aok
