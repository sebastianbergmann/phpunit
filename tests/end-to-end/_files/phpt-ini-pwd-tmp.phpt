--TEST--
{PWD} and {TMP} are substituted in the INI section
--INI--
docref_root={PWD}
docref_ext={TMP}
--FILE--
<?php declare(strict_types=1);
var_dump(ini_get('docref_root') === __DIR__);
var_dump(ini_get('docref_ext') === sys_get_temp_dir());
--EXPECT--
bool(true)
bool(true)
