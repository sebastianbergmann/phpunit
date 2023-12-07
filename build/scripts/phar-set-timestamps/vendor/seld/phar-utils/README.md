PHAR Utils
==========

PHAR file format utilities, for when PHP phars you up.

Installation
------------

`composer require seld/phar-utils`

API
---

### `Seld\PharUtils\Timestamps`

- `__construct($pharFile)`

  > Load a phar file in memory.

- `updateTimestamps($timestamp = null)`

  > Updates each file's unix timestamps in the PHAR so the PHAR signature
  > can be produced in a reproducible manner.

- `save($path, $signatureAlgo = '')`

  > Saves the updated phar file with an updated signature.
  > Algo must be one of `Phar::MD5`, `Phar::SHA1`, `Phar::SHA256`
  > or `Phar::SHA512`

### `Seld\PharUtils\Linter`

- `Linter::lint($pharFile)`

  > Lints all php files inside a given phar with the current PHP version.

Requirements
------------

PHP 5.3 and above

License
-------

PHAR Utils is licensed under the MIT License - see the LICENSE file for details
