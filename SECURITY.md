# Security Policy

## Reporting a vulnerability

If you believe you have found a security vulnerability in PHPUnit, please report it to us through coordinated disclosure.

**Please do not report security vulnerabilities through public GitHub issues, discussions, or pull requests.**

Instead, please email `sebastian@phpunit.de`.

Please include as much of the information listed below as you can to help us better understand and resolve the issue:

* The type of issue
* Full paths of source file(s) related to the manifestation of the issue
* The location of the affected source code (tag/branch/commit or direct URL)
* Any special configuration required to reproduce the issue
* Step-by-step instructions to reproduce the issue
* Proof-of-concept or exploit code (if possible)
* Impact of the issue, including how an attacker might exploit the issue

This information will help us triage your report more quickly.

## Scope

PHPUnit is a framework for writing as well as a command-line tool for running tests.
Writing and running tests is a development-time activity.
PHPUnit is developed with a focus on development environments and the command-line.
No specific testing or hardening with regard to using PHPUnit in an HTTP or web context, in a CI/CD context, or with untrusted input data is performed.

Over the years, security issues have been addressed in PHPUnit, for example [CVE-2017-9841](https://www.cve.org/CVERecord?id=CVE-2017-9841), [GHSA-vvj3-c3rp-c85p](https://github.com/sebastianbergmann/phpunit/security/advisories/GHSA-vvj3-c3rp-c85p), and [GHSA-qrr6-mg7r-m243](https://github.com/sebastianbergmann/phpunit/security/advisories/GHSA-qrr6-mg7r-m243).
I will continue to do my best to develop PHPUnit in a way that is safe and secure.
However, I will no longer treat bugs in PHPUnit as security issues.

### CI/CD Pipelines

Running tests in a CI/CD pipeline is a legitimate and common use of PHPUnit.
However, a CI/CD pipeline is not a sandbox, and PHPUnit is not designed to defend one.
Executing test code, production code, or a configuration for PHPUnit or PHP that originates from an untrusted source is equivalent to executing arbitrary code on the infrastructure that hosts the pipeline.
This class of risk is documented as [CICD-SEC-04: Poisoned Pipeline Execution](https://owasp.org/www-project-top-10-ci-cd-security-risks/CICD-SEC-04-Poisoned-Pipeline-Execution) in the OWASP Top 10 CI/CD Security Risks.

**If you run PHPUnit in your CI/CD pipeline with a configuration for PHPUnit or PHP, test code, and/or production code from untrusted sources, then your development process is broken.**

Running code from a pull request on your infrastructure is, by definition, remote code execution.

Protecting the pipeline is the responsibility of the operator of the pipeline:
limit which events trigger workflows, require review before workflows run on contributions from outside collaborators, isolate jobs that run untrusted code, and do not expose secrets to such jobs.

### Webserver

There is no reason why PHPUnit should be installed on a webserver and/or in a production environment.

**If you upload PHPUnit to a webserver then your deployment process is broken.**

On a more general note, if your `vendor` directory is publicly accessible on your webserver then your deployment process is also broken.

PHPUnit might contain functionality that intentionally exposes internal application data for debugging purposes.
If PHPUnit is used in a web application, the application developer is responsible for filtering inputs or escaping outputs as necessary and for verifying that the used functionality is safe for use within the intended context.
