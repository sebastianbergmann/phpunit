#!/usr/bin/env php
<?php declare(strict_types=1);
if ($argc !== 4 || !is_dir($argv[1]) || !is_file($argv[2])) {
    fwrite(
        STDERR,
        sprintf(
            '%s /path/to/directory /path/to/stub-template.php.in /path/to/output.phar' . PHP_EOL,
            $argv[0]
        )
    );

    exit(1);
}

if (!Phar::canWrite()) {
    fwrite(STDERR, 'Cannot create PHAR: phar.readonly is enabled' . PHP_EOL);

    exit(1);
}

$files   = files(realpath($argv[1]));
$classes = classes($files);

build(
    $argv[3],
    stub(file_get_contents($argv[2]), basename($argv[3]), $classes),
    $files
);

printf(
    'Created %s with %d files and %d classes, interfaces, traits, and enumerations' . PHP_EOL,
    $argv[3],
    count($files),
    count($classes)
);

/**
 * Returns all files in the directory, sorted by their path inside the PHAR
 *
 * @return array<string, string> path inside the PHAR => path on the filesystem
 */
function files(string $directory): array
{
    $files = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $path = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($directory) + 1));

        $files[$path] = $file->getPathname();
    }

    ksort($files, SORT_STRING);

    return $files;
}

/**
 * Returns the classes, interfaces, traits, and enumerations that are declared
 * in the PHP files, sorted by name
 *
 * @param array<string, string> $files
 *
 * @return array<string, string> name => path inside the PHAR
 */
function classes(array $files): array
{
    $classes = [];

    foreach ($files as $path => $file) {
        if (!str_ends_with($path, '.php')) {
            continue;
        }

        foreach (declarations(file_get_contents($file)) as $class) {
            if (isset($classes[$class])) {
                fwrite(
                    STDERR,
                    sprintf(
                        'Cannot create PHAR: %s is declared in both %s and %s' . PHP_EOL,
                        $class,
                        $classes[$class],
                        $path
                    )
                );

                exit(1);
            }

            $classes[$class] = $path;
        }
    }

    ksort($classes, SORT_STRING);

    return $classes;
}

/**
 * Returns the names of the classes, interfaces, traits, and enumerations
 * that are declared in the source code; a name that is declared more than
 * once, for instance conditionally, is only returned once
 *
 * @return list<string>
 */
function declarations(string $source): array
{
    $tokens       = PhpToken::tokenize($source);
    $count        = count($tokens);
    $namespace    = '';
    $declarations = [];

    for ($i = 0; $i < $count; $i++) {
        if ($tokens[$i]->is(T_NAMESPACE)) {
            $namespace = '';

            for ($i++; $i < $count && !$tokens[$i]->is([';', '{']); $i++) {
                if ($tokens[$i]->is([T_STRING, T_NAME_QUALIFIED])) {
                    $namespace = $tokens[$i]->text;
                }
            }

            continue;
        }

        if (!$tokens[$i]->is([T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {
            continue;
        }

        // Foo::class
        $previous = significantToken($tokens, $i, -1);

        if ($previous !== null && $previous->is(T_DOUBLE_COLON)) {
            continue;
        }

        // anonymous classes, methods named "class", named arguments named "class", ...
        $name = significantToken($tokens, $i, 1);

        if ($name === null || !$name->is(T_STRING)) {
            continue;
        }

        if ($namespace !== '') {
            $declarations[] = $namespace . '\\' . $name->text;
        } else {
            $declarations[] = $name->text;
        }
    }

    return array_values(array_unique($declarations));
}

/**
 * @param list<PhpToken> $tokens
 * @param -1|1           $direction
 */
function significantToken(array $tokens, int $position, int $direction): ?PhpToken
{
    for ($i = $position + $direction; isset($tokens[$i]); $i += $direction) {
        if (!$tokens[$i]->isIgnorable()) {
            return $tokens[$i];
        }
    }

    return null;
}

/**
 * Replaces ___PHAR___ with the alias of the PHAR and ___CLASSLIST___ with
 * the entries of an array that maps the names of classes, interfaces, traits,
 * and enumerations to the path of the file inside the PHAR that declares them
 *
 * @param array<string, string> $classes
 */
function stub(string $template, string $alias, array $classes): string
{
    $entries = [];

    foreach ($classes as $class => $path) {
        $entries[] = var_export($class, true) . ' => ' . var_export('/' . $path, true);
    }

    return str_replace(
        [
            '___PHAR___',
            '___CLASSLIST___',
        ],
        [
            $alias,
            implode(",\n                ", $entries),
        ],
        $template
    );
}

/**
 * @param array<string, string> $files path inside the PHAR => path on the filesystem
 */
function build(string $filename, string $stub, array $files): void
{
    if (file_exists($filename)) {
        unlink($filename);
    }

    $phar = new Phar($filename, 0, basename($filename));

    $phar->startBuffering();
    $phar->setStub($stub);
    $phar->setSignatureAlgorithm(Phar::SHA512);
    $phar->buildFromIterator(new ArrayIterator($files));
    $phar->stopBuffering();
}
