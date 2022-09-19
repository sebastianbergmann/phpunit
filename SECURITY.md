# Security Policy

PHPUnit is a framework for writing as well as a commandline tool for running tests. Writing and running tests is a development-time activity. There is no reason why PHPUnit should be installed on a webserver.

**If you upload PHPUnit to a webserver then your deployment process is broken. On a more general note, if your `vendor` directory is publicly accessible on your webserver then your deployment process is also broken.**

Please note that if you upload PHPUnit to a webserver "bad things" may happen. [You have been warned.](https://thephp.cc/articles/phpunit-a-security-risk)

## Security Contact Information

After the above, if you still would like to report a security vulnerability, please email `sebastian@phpunit.de`.
