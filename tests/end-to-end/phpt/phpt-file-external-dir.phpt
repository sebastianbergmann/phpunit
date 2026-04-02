--TEST--
GH-6025: FILE_EXTERNAL resolves __DIR__ and __FILE__ to external file location
--FILE_EXTERNAL--
../_files/file-external-dir/code.php
--EXPECTF--
%stests%eend-to-end%e_files%efile-external-dir
%stests%eend-to-end%e_files%efile-external-dir%ecode.php
