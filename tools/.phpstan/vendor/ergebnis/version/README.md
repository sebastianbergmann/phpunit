# version

[![Integrate](https://github.com/ergebnis/version/actions/workflows/integrate.yaml/badge.svg?branch=main)](https://github.com/ergebnis/version/actions/workflows/integrate.yaml)
[![Merge](https://github.com/ergebnis/version/actions/workflows/merge.yaml/badge.svg)](https://github.com/ergebnis/version/actions/workflows/merge.yaml)
[![Release](https://github.com/ergebnis/version/actions/workflows/release.yaml/badge.svg)](https://github.com/ergebnis/version/actions/workflows/release.yaml)
[![Renew](https://github.com/ergebnis/version/actions/workflows/renew.yaml/badge.svg)](https://github.com/ergebnis/version/actions/workflows/renew.yaml)

[![Code Coverage](https://codecov.io/gh/ergebnis/version/branch/main/graph/badge.svg)](https://codecov.io/gh/ergebnis/version)

[![Latest Stable Version](https://poser.pugx.org/ergebnis/version/v/stable)](https://packagist.org/packages/ergebnis/version)
[![Total Downloads](https://poser.pugx.org/ergebnis/version/downloads)](https://packagist.org/packages/ergebnis/version)
[![Monthly Downloads](https://poser.pugx.org/ergebnis/version/d/monthly)](https://packagist.org/packages/ergebnis/version)

This project provides a [`composer`](https://getcomposer.org) package with an abstraction of a [semantic version](https://semver.org).

## Installation

Run

```sh
composer require ergebnis/version
```

## Usage

This project comes with the following components:

- [`Ergebnis\Version\Version`](#version)
- [`Ergebnis\Version\Major`](#major)
- [`Ergebnis\Version\Minor`](#minor)
- [`Ergebnis\Version\Patch`](#patch)
- [`Ergebnis\Version\PreRelease`](#prerelease)
- [`Ergebnis\Version\BuildMetaData`](#buildmetadata)

### `Version`

#### Create a `Version` from a `string`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$version = Version\Version::fromString('1.2.3-alpha+build.9001');

echo $version->toString(); // 1.2.3

echo $version->major()->toString(); // 1
echo $version->minor()->toString(); // 2
echo $version->patch()->toString(); // 3
echo $version->preRelease()->toString(); // alpha
echo $version->buildMetaData()->toString(); // build.9001
```

#### Bump a `Version`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$version = Version\Version::fromString('1.2.3');

$one = $version->bumpMajor();

echo $one->toString(); // 2.0.0

$two = $version->bumpMinor();

echo $two->toString(); // 1.3.0

$three = $version->bumpPatch();

echo $three->toString(); // 1.2.4
```

#### Bump a `Version` with `PreRelease`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$version = Version\Version::fromString('1.2.3-alpha');

$one = $version->bumpMajor();

echo $one->toString(); // 2.0.0

$two = $version->bumpMinor();

echo $two->toString(); // 1.3.0

$three = $version->bumpPatch();

echo $three->toString(); // 1.2.3
```

#### Bump a `Version` with `BuildMetaData`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$version = Version\Version::fromString('1.2.3+build.9001');

$one = $version->bumpMajor();

echo $one->toString(); // 2.0.0

$two = $version->bumpMinor();

echo $two->toString(); // 1.3.0

$three = $version->bumpPatch();

echo $three->toString(); // 1.2.4
```

#### Compare a `Version` with another `Version`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\Version::fromString('1.2.3-alpha');
$two = Version\Version::fromString('1.2.3');
$three = Version\Version::fromString('1.2.4');
$four = Version\Version::fromString('1.2.4+build.9001');

$one->compare($two); // -1
$one->compare($one); // 0
$three->compare($one); // 1

$one->isSmallerThan($one); // false
$one->isSmallerThan($two); // true
$one->isSmallerThan($three); // true
$one->isSmallerThan($four); // true

$one->equals($two); // false
$one->equals($one); // true
$one->equals($three); // false
$three->equals($four); // true

$one->isGreaterThan($one); // false
$two->isGreaterThan($one); // true
$three->isGreaterThan($one); // true
$four->isGreaterThan($one); // true
```

### `Major`

#### Create a `Major` from an `int`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$major = Version\Major::fromInt(1);

echo $major->toString(); // 1
```

#### Create a `Major` from a `string`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$major = Version\Major::fromString('1');

echo $major->toString(); // 1
```

#### Bump a `Major`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\Major::fromString('1');

$two = $one->bump();

echo $two->toString(); // 2
```

#### Compare a `Major` with another `Major`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\Major::fromString('1');
$two = Version\Major::fromString('2');

$one->compare($two); // -1
$one->compare($one); // 0
$two->compare($one); // 1

$one->isSmallerThan($one); // false
$one->isSmallerThan($two); // true

$one->equals($two); // false
$one->equals($one); // true

$one->isGreaterThan($one); // false
$two->isGreaterThan($one); // true
```

### `Minor`

#### Create a `Minor` from an `int`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$minor = Version\Minor::fromInt(1);

echo $minor->toString(); // 1
```

#### Create a `Minor` from a `string`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$minor = Version\Minor::fromString('1');

echo $minor->toString(); // 1
```

#### Bump a `Minor`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\Minor::fromString('1');

$two = $one->bump();

echo $two->toString(); // 2
```

#### Compare a `Minor` with another `Minor`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\Minor::fromString('1');
$two = Version\Minor::fromString('2');

$one->compare($two); // -1
$one->compare($one); // 0
$two->compare($one); // 1

$one->isSmallerThan($one); // false
$one->isSmallerThan($two); // true

$one->equals($two); // false
$one->equals($one); // true

$one->isGreaterThan($one); // false
$two->isGreaterThan($one); // true
```

### `Patch`

#### Create a `Patch` from an `int`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$patch = Version\Patch::fromInt(1);

echo $patch->toString(); // 1
```

#### Create a `Patch` from a `string`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$patch = Version\Patch::fromString('1');

echo $patch->toString(); // 1
```

#### Bump a `Patch`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\Patch::fromString('1');

$two = $one->bump();

echo $two->toString(); // 2
```

#### Compare a `Patch` with another `Patch`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\Patch::fromString('1');
$two = Version\Patch::fromString('2');

$one->compare($two); // -1
$one->compare($one); // 0
$two->compare($one); // 1

$one->isSmallerThan($one); // false
$one->isSmallerThan($two); // true

$one->equals($two); // false
$one->equals($one); // true

$one->isGreaterThan($one); // false
$two->isGreaterThan($one); // true
```

### `PreRelease`

#### Create a `PreRelease` from a `string`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$preRelease = Version\PreRelease::fromString('alpha');

echo $preRelease->toString(); // alpha
```

#### Create an empty `PreRelease`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$preRelease = Version\PreRelease::empty();

echo $preRelease->toString(); // empty
```

#### Compare a `PreRelease` with another `PreRelease`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\PreRelease::fromString('alpha');
$two = Version\PreRelease::fromString('rc.1');

$one->compare($two); // -1
$one->compare($one); // 0
$two->compare($one); // 1

$one->isSmallerThan($one); // false
$one->isSmallerThan($two); // true

$one->equals($two); // false
$one->equals($one); // true

$one->isGreaterThan($one); // false
$two->isGreaterThan($one); // true
```

### `BuildMetaData`

#### Create a `BuildMetaData` from a `string`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$buildMetaData = Version\BuildMetaData::fromString('build.9001');

echo $buildMetaData->toString(); // build.9001
```

#### Create an empty `BuildMetaData`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$buildMetaData = Version\BuildMetaData::empty();

echo $buildMetaData->toString(); // empty
```

#### Compare a `BuildMetaData` with another `BuildMetaData`

```php
<?php

declare(strict_types=1);

use Ergebnis\Version;

$one = Version\BuildMetaData::fromString('build.9001');
$two = Version\BuildMetaData::fromString('build.9000');

$one->equals($two); // false
$one->equals($one); // true
```

## Changelog

The maintainers of this project record notable changes to this project in a [changelog](CHANGELOG.md).

## Contributing

The maintainers of this project suggest following the [contribution guide](.github/CONTRIBUTING.md).

## Code of Conduct

The maintainers of this project ask contributors to follow the [code of conduct](https://github.com/ergebnis/.github/blob/main/CODE_OF_CONDUCT.md).

## General Support Policy

The maintainers of this project provide limited support.

You can support the maintenance of this project by [sponsoring @ergebnis](https://github.com/sponsors/ergebnis).

## PHP Version Support Policy

This project currently supports the following PHP versions:

- [PHP 7.4](https://www.php.net/releases/#7.4.0) (has reached its end of life on November 28, 2022)
- [PHP 8.0](https://www.php.net/releases/#8.0.0) (has reached its end of life on November 26, 2023)
- [PHP 8.1](https://www.php.net/releases/#8.1.0) (has reached its end of life on December 31, 2025)
- [PHP 8.2](https://www.php.net/releases/#8.2.0)
- [PHP 8.3](https://www.php.net/releases/#8.3.0)
- [PHP 8.4](https://www.php.net/releases/#8.4.0)
- [PHP 8.5](https://www.php.net/releases/#8.5.0)

The maintainers of this project add support for a PHP version following its initial release and _may_ drop support for a PHP version when it has reached its [end of life](https://www.php.net/supported-versions.php).

## Security Policy

This project has a [security policy](.github/SECURITY.md).

## License

This project uses the [MIT license](LICENSE.md).

## Social

Follow [@localheinz](https://twitter.com/intent/follow?screen_name=localheinz) and [@ergebnis](https://twitter.com/intent/follow?screen_name=ergebnis) on Twitter.
