[![PHPUnit](.github/img/phpunit.svg)](https://phpunit.de/?ref=github)

[![CI Status](https://github.com/sebastianbergmann/phpunit/workflows/CI/badge.svg)](https://github.com/sebastianbergmann/phpunit/actions)
[![codecov](https://codecov.io/gh/sebastianbergmann/phpunit/branch/main/graph/badge.svg?token=0yzBUK8Wri)](https://codecov.io/gh/sebastianbergmann/phpunit)
[![Latest Stable Version](https://poser.pugx.org/phpunit/phpunit/v)](https://packagist.org/packages/phpunit/phpunit)
[![Total Downloads](https://poser.pugx.org/phpunit/phpunit/downloads)](https://packagist.org/packages/phpunit/phpunit/stats)
[![Monthly Downloads](https://poser.pugx.org/phpunit/phpunit/d/monthly)](https://packagist.org/packages/phpunit/phpunit/stats)
[![Daily Downloads](https://poser.pugx.org/phpunit/phpunit/d/daily)](https://packagist.org/packages/phpunit/phpunit/stats)

# PHPUnit

PHPUnit is a programmer-oriented testing framework for PHP.
It is an instance of the xUnit architecture for unit testing frameworks.

## Installation

We distribute a [PHP Archive (PHAR)](https://php.net/phar) that has all required dependencies of PHPUnit bundled in a single file:

```bash
$ wget https://phar.phpunit.de/phpunit-X.Y.phar

$ php phpunit-X.Y.phar --version
```

Please replace `X.Y` with the version of PHPUnit you are interested in.

Alternatively, you may use [Composer](https://getcomposer.org/) to download and install PHPUnit as well as its dependencies.
Please refer to the [documentation](https://phpunit.de/documentation.html?ref=github) for details on how to install PHPUnit.

## Contribute

Please refer to [CONTRIBUTING.md](https://github.com/sebastianbergmann/phpunit/blob/main/.github/CONTRIBUTING.md) for information on how to contribute to PHPUnit and its related projects.

A big "Thank you!" to everyone who has contributed to PHPUnit!
You can find a detailed list of contributors on every PHPUnit related package on GitHub.

Here is a list of all components that are primarily developed and maintained by [Sebastian Bergmann](https://sebastian-bergmann.de/open-source.html?ref=github):

* [phpunit/phpunit](https://github.com/sebastianbergmann/phpunit/graphs/contributors)
* [phpunit/php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage/graphs/contributors)
* [phpunit/php-file-iterator](https://github.com/sebastianbergmann/php-file-iterator/graphs/contributors)
* [phpunit/php-invoker](https://github.com/sebastianbergmann/php-invoker/graphs/contributors)
* [phpunit/php-text-template](https://github.com/sebastianbergmann/php-text-template/graphs/contributors)
* [phpunit/php-timer](https://github.com/sebastianbergmann/php-timer/graphs/contributors)
* [sebastian/cli-parser](https://github.com/sebastianbergmann/cli-parser/graphs/contributors)
* [sebastian/comparator](https://github.com/sebastianbergmann/comparator/graphs/contributors)
* [sebastian/complexity](https://github.com/sebastianbergmann/complexity/graphs/contributors)
* [sebastian/diff](https://github.com/sebastianbergmann/diff/graphs/contributors)
* [sebastian/environment](https://github.com/sebastianbergmann/environment/graphs/contributors)
* [sebastian/exporter](https://github.com/sebastianbergmann/exporter/graphs/contributors)
* [sebastian/global-state](https://github.com/sebastianbergmann/global-state/graphs/contributors)
* [sebastian/lines-of-code](https://github.com/sebastianbergmann/lines-of-code/graphs/contributors)
* [sebastian/object-enumerator](https://github.com/sebastianbergmann/object-enumerator/graphs/contributors)
* [sebastian/object-reflector](https://github.com/sebastianbergmann/object-reflector/graphs/contributors)
* [sebastian/recursion-context](https://github.com/sebastianbergmann/recursion-context/graphs/contributors)
* [sebastian/type](https://github.com/sebastianbergmann/type/graphs/contributors)
* [sebastian/version](https://github.com/sebastianbergmann/version/graphs/contributors)

A very special thanks to everyone who has contributed to the [PHPUnit Manual](https://github.com/sebastianbergmann/phpunit-documentation-english/graphs/contributors).

In addition to the components listed above, PHPUnit depends on the components listed below:

* [myclabs/deep-copy](https://github.com/myclabs/DeepCopy/graphs/contributors)
* [nikic/php-parser](https://github.com/nikic/php-parser/graphs/contributors)
* [phar-io/manifest](https://github.com/phar-io/manifest/graphs/contributors)
* [phar-io/version](https://github.com/phar-io/version/graphs/contributors)
* [staabm/side-effects-detector](https://github.com/staabm/side-effects-detector/graphs/contributors)
* [theseer/tokenizer](https://github.com/theseer/tokenizer/graphs/contributors)

These tools are used to develop PHPUnit:

* [Composer](https://getcomposer.org/)
* [Phive](https://phar.io/)
* [PHP Autoload Builder](https://github.com/theseer/Autoload/)
* [PHP-CS-Fixer](https://cs.symfony.com/)
* [PHP-Scoper](https://github.com/humbug/php-scoper)
* [PHPStan](https://phpstan.org/)

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
