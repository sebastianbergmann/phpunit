# clue/reactphp-ndjson

[![CI status](https://github.com/clue/reactphp-ndjson/actions/workflows/ci.yml/badge.svg)](https://github.com/clue/reactphp-ndjson/actions)
[![installs on Packagist](https://img.shields.io/packagist/dt/clue/ndjson-react?color=blue&label=installs%20on%20Packagist)](https://packagist.org/packages/clue/ndjson-react)
[![code coverage](https://img.shields.io/badge/code%20coverage-100%25-success)](#tests)

Streaming newline-delimited JSON ([NDJSON](http://ndjson.org/)) parser and encoder for [ReactPHP](https://reactphp.org/).

[NDJSON](http://ndjson.org/) can be used to store multiple JSON records in a
file to store any kind of (uniform) structured data, such as a list of user
objects or log entries. It uses a simple newline character between each
individual record and as such can be both used for efficient persistence and
simple append-style operations. This also allows it to be used in a streaming
context, such as a simple inter-process communication (IPC) protocol or for a
remote procedure call (RPC) mechanism. This library provides a simple
streaming API to process very large NDJSON files with thousands or even millions
of rows efficiently without having to load the whole file into memory at once.

* **Standard interfaces** -
  Allows easy integration with existing higher-level components by implementing
  ReactPHP's standard streaming interfaces.
* **Lightweight, SOLID design** -
  Provides a thin abstraction that is [*just good enough*](https://en.wikipedia.org/wiki/Principle_of_good_enough)
  and does not get in your way.
  Builds on top of well-tested components and well-established concepts instead of reinventing the wheel.
* **Good test coverage** -
  Comes with an [automated tests suite](#tests) and is regularly tested in the *real world*.

**Table of contents**

* [Support us](#support-us)
* [NDJSON format](#ndjson-format)
* [Usage](#usage)
    * [Decoder](#decoder)
    * [Encoder](#encoder)
* [Install](#install)
* [Tests](#tests)
* [License](#license)
* [More](#more)

## Support us

We invest a lot of time developing, maintaining, and updating our awesome
open-source projects. You can help us sustain this high-quality of our work by
[becoming a sponsor on GitHub](https://github.com/sponsors/clue). Sponsors get
numerous benefits in return, see our [sponsoring page](https://github.com/sponsors/clue)
for details.

Let's take these projects to the next level together! 🚀

## NDJSON format

NDJSON ("Newline-Delimited JSON" or sometimes referred to as "JSON lines") is a
very simple text-based format for storing a large number of records, such as a
list of user records or log entries.

```JSON
{"name":"Alice","age":30,"comment":"Yes, I like cheese"}
{"name":"Bob","age":50,"comment":"Hello\nWorld!"}
```

If you understand JSON and you're now looking at this newline-delimited JSON for
the first time, you should already know everything you need to know to
understand NDJSON: As the name implies, this format essentially consists of
individual lines where each individual line is any valid JSON text and each line
is delimited with a newline character.

This example uses a list of user objects where each user has some arbitrary
properties. This can easily be adjusted for many different use cases, such as
storing for example products instead of users, assigning additional properties
or having a significantly larger number of records. You can edit NDJSON files in
any text editor or use them in a streaming context where individual records
should be processed. Unlike normal JSON files, adding a new log entry to this
NDJSON file does not require modification of this file's structure (note there's
no "outer array" to be modified). This makes it a perfect fit for a streaming
context, for line-oriented CLI tools (such as `grep` and others) or for a logging
context where you want to append records at a later time. Additionally, this
also allows it to be used in a streaming context, such as a simple inter-process
communication (IPC) protocol or for a remote procedure call (RPC) mechanism.

The newline character at the end of each line allows for some really simple
*framing* (detecting individual records). While each individual line is valid
JSON, the complete file as a whole is technically no longer valid JSON, because
it contains multiple JSON texts. This implies that for example calling PHP's
`json_decode()` on this complete input would fail because it would try to parse
multiple records at once. Likewise, using "pretty printing" JSON
(`JSON_PRETTY_PRINT`) is not allowed because each JSON text is limited to exactly
one line. On the other hand, values containing newline characters (such as the
`comment` property in the above example) do not cause issues because each newline
within a JSON string will be represented by a `\n` instead.

One common alternative to NDJSON would be Comma-Separated Values (CSV).
If you want to process CSV files, you may want to take a look at the related
project [clue/reactphp-csv](https://github.com/clue/reactphp-csv) instead:

```
name,age,comment
Alice,30,"Yes, I like cheese"
Bob,50,"Hello
World!"
```

CSV may look slightly simpler, but this simplicity comes at a price. CSV is
limited to untyped, two-dimensional data, so there's no standard way of storing
any nested structures or to differentiate a boolean value from a string or
integer. Field names are sometimes used, sometimes they're not
(application-dependant). Inconsistent handling for fields that contain
separators such as `,` or spaces or line breaks (see the `comment` field above)
introduce additional complexity and its text encoding is usually undefined,
Unicode (or UTF-8) is unlikely to be supported and CSV files often use ISO
8859-1 encoding or some variant (again application-dependant).

While NDJSON helps avoiding many of CSV's shortcomings, it is still a
(relatively) young format while CSV files have been used in production systems
for decades. This means that if you want to interface with an existing system,
you may have to rely on the format that's already supported. If you're building
a new system, using NDJSON is an excellent choice as it provides a flexible way
to process individual records using a common text-based format that can include
any kind of structured data.

## Usage

### Decoder

The `Decoder` (parser) class can be used to make sure you only get back
complete, valid JSON elements when reading from a stream.
It wraps a given
[`ReadableStreamInterface`](https://github.com/reactphp/stream#readablestreaminterface)
and exposes its data through the same interface, but emits the JSON elements
as parsed values instead of just chunks of strings:

```
{"name":"test","active":true}
{"name":"hello w\u00f6rld","active":true}
```

```php
$stdin = new React\Stream\ReadableResourceStream(STDIN);

$ndjson = new Clue\React\NDJson\Decoder($stdin);

$ndjson->on('data', function ($data) {
    // $data is a parsed element from the JSON stream
    // line 1: $data = (object)array('name' => 'test', 'active' => true);
    // line 2: $data = (object)array('name' => 'hello wörld', 'active' => true);
    var_dump($data);
});
```

ReactPHP's streams emit chunks of data strings and make no assumption about their lengths.
These chunks do not necessarily represent complete JSON elements, as an
element may be broken up into multiple chunks.
This class reassembles these elements by buffering incomplete ones.

The `Decoder` supports the same optional parameters as the underlying
[`json_decode()`](https://www.php.net/manual/en/function.json-decode.php) function.
This means that, by default, JSON objects will be emitted as a `stdClass`.
This behavior can be controlled through the optional constructor parameters:

```php
$ndjson = new Clue\React\NDJson\Decoder($stdin, true);

$ndjson->on('data', function ($data) {
    // JSON objects will be emitted as assoc arrays now
});
```

Additionally, the `Decoder` limits the maximum buffer size (maximum line
length) to avoid buffer overflows due to malformed user input. Usually, there
should be no need to change this value, unless you know you're dealing with some
unreasonably long lines. It accepts an additional argument if you want to change
this from the default of 64 KiB:

```php
$ndjson = new Clue\React\NDJson\Decoder($stdin, false, 512, 0, 64 * 1024);
```

If the underlying stream emits an `error` event or the plain stream contains
any data that does not represent a valid NDJson stream,
it will emit an `error` event and then `close` the input stream:

```php
$ndjson->on('error', function (Exception $error) {
    // an error occured, stream will close next
});
```

If the underlying stream emits an `end` event, it will flush any incomplete
data from the buffer, thus either possibly emitting a final `data` event
followed by an `end` event on success or an `error` event for
incomplete/invalid JSON data as above:

```php
$ndjson->on('end', function () {
    // stream successfully ended, stream will close next
});
```

If either the underlying stream or the `Decoder` is closed, it will forward
the `close` event:

```php
$ndjson->on('close', function () {
    // stream closed
    // possibly after an "end" event or due to an "error" event
});
```

The `close(): void` method can be used to explicitly close the `Decoder` and
its underlying stream:

```php
$ndjson->close();
```

The `pipe(WritableStreamInterface $dest, array $options = array(): WritableStreamInterface`
method can be used to forward all data to the given destination stream.
Please note that the `Decoder` emits decoded/parsed data events, while many
(most?) writable streams expect only data chunks:

```php
$ndjson->pipe($logger);
```

For more details, see ReactPHP's
[`ReadableStreamInterface`](https://github.com/reactphp/stream#readablestreaminterface).

### Encoder

The `Encoder` (serializer) class can be used to make sure anything you write to
a stream ends up as valid JSON elements in the resulting NDJSON stream.
It wraps a given
[`WritableStreamInterface`](https://github.com/reactphp/stream#writablestreaminterface)
and accepts its data through the same interface, but handles any data as complete
JSON elements instead of just chunks of strings:

```php
$stdout = new React\Stream\WritableResourceStream(STDOUT);

$ndjson = new Clue\React\NDJson\Encoder($stdout);

$ndjson->write(array('name' => 'test', 'active' => true));
$ndjson->write(array('name' => 'hello wörld', 'active' => true));
```
```
{"name":"test","active":true}
{"name":"hello w\u00f6rld","active":true}
```

The `Encoder` supports the same parameters as the underlying
[`json_encode()`](https://www.php.net/manual/en/function.json-encode.php) function.
This means that, by default, Unicode characters will be escaped in the output.
This behavior can be controlled through the optional constructor parameters:

```php
$ndjson = new Clue\React\NDJson\Encoder($stdout, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$ndjson->write('hello wörld');
```
```
"hello wörld"
```

Note that trying to pass the `JSON_PRETTY_PRINT` option will yield an
`InvalidArgumentException` because it is not compatible with NDJSON.

If the underlying stream emits an `error` event or the given data contains
any data that can not be represented as a valid NDJSON stream,
it will emit an `error` event and then `close` the input stream:

```php
$ndjson->on('error', function (Exception $error) {
    // an error occured, stream will close next
});
```

If either the underlying stream or the `Encoder` is closed, it will forward
the `close` event:

```php
$ndjson->on('close', function () {
    // stream closed
    // possibly after an "end" event or due to an "error" event
});
```

The `end(mixed $data = null): void` method can be used to optionally emit
any final data and then soft-close the `Encoder` and its underlying stream:

```php
$ndjson->end();
```

The `close(): void` method can be used to explicitly close the `Encoder` and
its underlying stream:

```php
$ndjson->close();
```

For more details, see ReactPHP's
[`WritableStreamInterface`](https://github.com/reactphp/stream#writablestreaminterface).

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org/).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This project follows [SemVer](https://semver.org/).
This will install the latest supported version:

```bash
composer require clue/ndjson-react:^1.3
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 8+ and
HHVM.
It's *highly recommended to use the latest supported PHP version* for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org/):

```bash
composer install
```

To run the test suite, go to the project root and run:

```bash
vendor/bin/phpunit
```

## License

This project is released under the permissive [MIT license](LICENSE).

> Did you know that I offer custom development services and issuing invoices for
  sponsorships of releases and for contributions? Contact me (@clue) for details.

## More

* If you want to learn more about processing streams of data, refer to the documentation of
  the underlying [react/stream](https://github.com/reactphp/stream) component.

* If you want to process compressed NDJSON files (`.ndjson.gz` file extension),
  you may want to use [clue/reactphp-zlib](https://github.com/clue/reactphp-zlib)
  on the compressed input stream before passing the decompressed stream to the NDJSON decoder.

* If you want to create compressed NDJSON files (`.ndjson.gz` file extension),
  you may want to use [clue/reactphp-zlib](https://github.com/clue/reactphp-zlib)
  on the resulting NDJSON encoder output stream before passing the compressed
  stream to the file output stream.

* If you want to concurrently process the records from your NDJSON stream,
  you may want to use [clue/reactphp-flux](https://github.com/clue/reactphp-flux)
  to concurrently process many (but not too many) records at once.

* If you want to process structured data in the more common text-based format,
  you may want to use [clue/reactphp-csv](https://github.com/clue/reactphp-csv)
  to process Comma-Separated-Values (CSV) files (`.csv` file extension).
