--TEST--
PHPT tests run with deterministic INI defaults
--FILE--
<?php declare(strict_types=1);
var_dump(ini_get('date.timezone'));
var_dump(ini_get('display_startup_errors'));
var_dump(ini_get('ignore_repeated_errors'));
var_dump(ini_get('precision'));
var_dump(ini_get('serialize_precision'));
var_dump(0.1 + 0.2);
--EXPECT--
string(3) "UTC"
string(1) "1"
string(1) "0"
string(2) "14"
string(2) "-1"
float(0.30000000000000004)
