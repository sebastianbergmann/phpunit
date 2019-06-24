You can't unset values in foreach, because it lead to segfault.

why do unset in an iterate array bad:
- http://www.justskins.com/forums/39036-new-unsetting-key-21835.html

need fix unset in foreach:
- vendor/phpunit/php-code-coverage/src/CodeCoverage.php:932
- vendor/phpunit/phpunit/src/Framework/TestResult.php:231
- vendor/phpunit/phpunit/src/Util/PHP/DefaultPhpProcess.php:71

fix unset example:
- php/sportsbook/vendor/phpunit/phpunit/src/Framework/TestSuite.php:568
