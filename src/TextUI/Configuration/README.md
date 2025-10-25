(this file can be removed before merging)

SourceMapper Implementation Notes
=================================

The primary goal is to ensure that the source mapper needn't iterate over all
included files recursively whenever the source map is required (for example
when a deprecation is encountered PHPUnit needs to know if the deprecation was
issued from source code within the project's responsiblity - i.e. source that
is mapped). We can determine if a file is within the included source by
converting the glob-patterns in the `<directory>` element to regexes.

Currently the `<directory>` element in `<include>` and `<exclude>` has the 
attributes `prefix` and `suffix` and we also have `<file>` which specifies a
single file.

This is more complicated than it could be:

- Current matching/traversal logic depends on PHP's `glob` function - the implementation
  of which is not consistent across platforms and which has a number of
  rarely-used operators which while not common, would present a B/C break if
  they were removed.





How it works currently
----------------------

```php
# src/TextUI/Configuration/SourceMapper.php
foreach ($includeDirectories as $path => [$prefixes, $suffixes]) {
    foreach ((new FileIteratorFacade)->getFilesAsArray($path, $suffixes, $prefixes) as $file) {
```

```php
# vendor/phpunit/php-file-iterator/src/Facade.php
public function getFilesAsArray(array|string $paths, array|string $suffixes = '', array|string $prefixes = '', array $exclude = []): array
{
    $iterator = (new Factory)->getFileIterator($paths, $suffixes, $prefixes, $exclude);
```

```php
# vendor/phpunit/php-file-iterator/src/Factory.php
```

The Factory:

- resolves (expands) any wildcards to concrete **file** paths
- iterates over the paths, ignoring any directories (by checking `is_dir`)[1]
- create a new PHPUnit `Iterator` passing a recursive iterator iterator
  directory iterator... (the iterator that iterates over the directories) that
  **follows symlinks and skips dots**.
- the iterator also Excludes the excluded `<directory>` elements **but does
  not take into account the prefixes or suffixes**.

The Iterator:

- Filters out paths if `realpath` returns `false`.
- Filters out hidden paths at the root of the resovled path.
- Filters out concretee paths based on the `basename` and any given suffix or
  prefix.

Features
--------

- Symlinks: 
- Skips hidden files

Assumptions (to verify)
-----------------------

The meaning of `<directory prefix="Test" suffix=".phpt">tests/</directory>`
means "all files in the `tests` directory whose (base)names begin with `Test` and
end with `.phpt`, for example `tests/Foobar/Barfoo/TestBar.phpt` would be a
match.

While `<file>tests/Bar/Foo.php</file>` would be a literal reference to a
single file.

Option 1: Simplify
------------------

Instead of having `<file>` and `<directory>` elements we can simplify them to
a single element that has no attributes:

```xml
<include>
   <pattern>tests/**/Test*.php</pattern>
   <pattern>tests/Bar/Foo.php</pattern>
</include>
```

Would be equivalent to:

```xml
<include>
   <directory prefix="Test" suffix=".php">tests/</directory>
   <file>tests/Bar/Foo.php</file>
</include>
```

Option 2: Preserve
------------------

In this case we preserve the existing API (but accept that the glob
implementation has changed, though it will work in 99% of cases the behavior
will change vs. the current implementation).

```xml
<directory prefix="Test" suffix=".php">tests/</directory>
```

Would be: `tests/**/Test*.php`

```xml
<directory>tests/</directory>
```

Would be: `tests/**/*`

```xml
<directory>tests</directory>
```

Would be: `tests/**/*`

```xml
<directory>tests/**/Command/*.php</directory>
```

Is _probably_ a user mistake and would translate to: `tests/**/Command/*.php/`
(they'd probably intended `<directory suffix=".php">tests/**/Command`).


