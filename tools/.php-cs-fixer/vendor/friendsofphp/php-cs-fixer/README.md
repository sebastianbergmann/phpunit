<p align="center">
    <a href="https://cs.symfony.com">
        <img src="./logo.png" title="PHP CS Fixer" alt="PHP CS Fixer logo">
    </a>
</p>

# PHP Coding Standards Fixer

The PHP Coding Standards Fixer (PHP CS Fixer) fixes your code to follow the standards.

If you are already using a linter to identify coding standards problems in your
code, you know that fixing them by hand is tedious, especially on large
projects. This tool not only detects them, but also fixes them for you.

PHP CS Fixer has built-in rule sets, whether you want to follow PHP coding standards as defined by [PHP-FIG's PER Coding Style](https://www.php-fig.org/per/coding-style/) - [`@PER-CS`](./doc/ruleSets/PER-CS.rst),
a wide community like the [Symfony](https://symfony.com/doc/current/contributing/code/standards.html) - [`@Symfony`](./doc/ruleSets/Symfony.rst),
or our opinionated one - [@PhpCsFixer](./doc/ruleSets/PhpCsFixer.rst).
You can also define your (team's) style through the [configuration file](./doc/config.rst).

PHP CS Fixer can not only unify the style of your code, but also help to modernise your codebase towards
newer PHP (e.g. [`@autoPHPMigration`](./doc/ruleSets/AutoPHPMigration.rst) and [`@autoPHPMigration:risky`](./doc/ruleSets/AutoPHPMigrationRisky.rst)) and newer PHPUnit (e.g. [`@autoPHPUnitMigration:risky`](./doc/ruleSets/AutoPHPUnitMigrationRisky.rst)).

There are also [`@auto`](./doc/ruleSets/Auto.rst) and [`@auto:risky`](./doc/ruleSets/AutoRisky.rst) that aim to provide good base rules.

## Supported PHP Versions

* PHP 7.4 - PHP 8.5

> [!NOTE]
> Each new PHP version requires a huge effort to support the new syntax.
> That's why the latest PHP version might not be supported yet. If you need it,
> please consider supporting the project in any convenient way, for example,
> with code contributions or reviewing existing PRs. To run PHP CS Fixer on yet
> unsupported versions "at your own risk" - use `--allow-unsupported-php-version=yes` option.

## Documentation

### Installation

The recommended way to install PHP CS Fixer is to use [Composer](https://getcomposer.org/download/):

```sh
composer require --dev friendsofphp/php-cs-fixer
## or when facing conflicts in dependencies:
composer require --dev php-cs-fixer/shim
```

For more details and other installation methods (also with Docker or behind CI), see
[installation instructions](./doc/installation.rst).

### Usage

Assuming you installed PHP CS Fixer as instructed above, you can
initialise base config for your project by using following command:

```sh
./vendor/bin/php-cs-fixer init
```

To automatically fix your project, or only check against the need of changes, run:

```sh
./vendor/bin/php-cs-fixer fix
./vendor/bin/php-cs-fixer check
```

See [usage](./doc/usage.rst), list of [built-in rules](./doc/rules/index.rst), list of [rule sets](./doc/ruleSets/index.rst)
and [configuration file](./doc/config.rst) documentation for more details.

If you need to apply code styles that are not built-in into the tool, you can
[create custom rules](./doc/custom_rules.rst).

## Editor Integration

Native support exists for:

* [PhpStorm](https://www.jetbrains.com/help/phpstorm/using-php-cs-fixer.html)

Community plugins exist for:

* [NetBeans](https://plugins.netbeans.apache.org/catalogue/?id=36)
* [Sublime Text](https://github.com/benmatselby/sublime-phpcs)
* [Vim](https://github.com/stephpy/vim-php-cs-fixer)
* [VS Code](https://github.com/junstyle/vscode-php-cs-fixer)

## Community

The PHP CS Fixer is maintained on GitHub at <https://github.com/PHP-CS-Fixer/PHP-CS-Fixer>.
Contributions, bug reports and ideas about new features are welcome there.

You can reach us in the [GitHub Discussions](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/discussions/) regarding the
project, configuration, possible improvements, ideas and questions.

## Contribute

The tool comes with quite a few built-in fixers, but everyone is more than
welcome to [contribute](./CONTRIBUTING.md) more of them.
