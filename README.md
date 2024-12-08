[![PHPUnit](.github/img/phpunit.svg)](https://phpunit.de/?ref=github)

[![CI Status](https://github.com/sebastianbergmann/phpunit/workflows/CI/badge.svg)](https://github.com/sebastianbergmann/phpunit/actions)
[![codecov](https://codecov.io/gh/sebastianbergmann/phpunit/branch/main/graph/badge.svg?token=0yzBUK8Wri)](https://codecov.io/gh/sebastianbergmann/phpunit)
[![Latest Stable Version](https://poser.pugx.org/phpunit/phpunit/v)](https://packagist.org/packages/phpunit/phpunit)
[![Total Downloads](https://poser.pugx.org/phpunit/phpunit/downloads)](https://packagist.org/packages/phpunit/phpunit)
[![Monthly Downloads](https://poser.pugx.org/phpunit/phpunit/d/monthly)](https://packagist.org/packages/phpunit/phpunit)
[![Daily Downloads](https://poser.pugx.org/phpunit/phpunit/d/daily)](https://packagist.org/packages/phpunit/phpunit)

# PHPUnit

PHPUnit is a programmer-oriented testing framework for PHP.
It is an instance of the xUnit architecture for unit testing frameworks.

## Installation

We distribute a [PHP Archive (PHAR)](https://php.net/phar) that has all required (as well as some optional) dependencies of PHPUnit bundled in a single file:

```bash
$ wget https://phar.phpunit.de/phpunit-X.Y.phar

$ php phpunit-X.Y.phar --version
```

Please replace `X.Y` with the version of PHPUnit you are interested in.

Alternatively, you may use [Composer](https://getcomposer.org/) to download and install PHPUnit as well as its dependencies.
Please refer to the [documentation](https://phpunit.de/documentation.html?ref=github) for details on how to install PHPUnit.

## Contribute

Please refer to [CONTRIBUTING.md](https://github.com/sebastianbergmann/phpunit/blob/main/.github/CONTRIBUTING.md) for information on how to contribute to PHPUnit and its related projects.

Thanks to everyone who has contributed to PHPUnit! You can find a detailed list of contributors on every PHPUnit related package on GitHub.
This list shows only the major components:

* [PHPUnit](https://github.com/sebastianbergmann/phpunit/graphs/contributors)
* [php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage/graphs/contributors)

A very special thanks to everyone who has contributed to the [documentation](https://github.com/sebastianbergmann/phpunit-documentation-english/graphs/contributors).

## Sponsors

It has taken [Sebastian Bergmann](https://sebastian-bergmann.de/open-source.html?ref=github) thousands of hours to develop, test, and support PHPUnit.
[**You can sponsor his Open Source work through GitHub Sponsors**](https://github.com/sponsors/sebastianbergmann), for example.

These businesses support Sebastian Bergmann's work on PHPUnit:

<table>
    <tbody>
        <tr>
            <td style="width: 30%; vertical-align: middle;"><a href="https://www.bubbleshooter.net/"><img alt="Bubble Shooter" src=".github/img/bubble-shooter.png" style="width: 200px;"/></a></td>
            <td style="width: 30%; vertical-align: middle;"><a href="https://www.in2it.be/phpunit-supporter/"><img alt="in2it vof" src=".github/img/in2it.svg" style="width: 200px;"/></a></td>
            <td style="width: 30%; vertical-align: middle;"><a href="https://roave.com/"><img alt="Roave" src=".github/img/roave.svg" style="width: 200px;"/></a></td>
        </tr>
        <tr>
            <td style="width: 30%; vertical-align: middle;"><a href="https://route4me.com/"><img alt="Route4Me" src=".github/img/route4me.svg" style="width: 200px;"/></a></td>
            <td style="width: 30%; vertical-align: middle;"><a href="https://testmo.com/"><img alt="Testmo GmbH" src=".github/img/testmo.svg" style="width: 200px;"/></a></td>
            <td style="width: 30%; vertical-align: middle;"><a href="https://tideways.com/"><img alt="Tideways GmbH" src=".github/img/tideways.svg" style="width: 200px;"/></a></td>
        </tr>
        <tr>
            <td style="width: 30%; vertical-align: middle;"><a href="https://typo3.com/"><img alt="TYPO3 GmbH" src=".github/img/typo3.svg" style="width: 200px;"/></a></td>
            <td style="width: 30%; vertical-align: middle;"><a href="https://vema-eg.de/"><img alt="VEMA Versicherungsmakler Genossenschaft eG" src=".github/img/vema.svg" style="width: 200px;"/></a></td>
        </tr>
    </tbody>
</table>

Would you like to see your logo here as well as on the [PHPUnit website](https://phpunit.de/sponsors.html?ref=github)?
Contact Sebastian Bergmann at [sponsoring@phpunit.de](mailto:sponsoring@phpunit.de) to learn more about how you can support his work on PHPUnit.

Whether you are a CEO, CFO, CTO, or a developer: your company surely depends on Open Source software.
[It is time to pay your share](https://opensourcepledge.com/) and support maintainers like [Sebastian Bergmann](https://sebastian-bergmann.de/open-source.html?ref=github).
