<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use function assert;
use function file_get_contents;
use function ini_get;
use function is_file;
use function strtolower;
use PhpParser\Error;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use ReflectionMethod;
use SebastianBergmann\Environment\Runtime;

/**
 * PHP discards doc comments when OPcache is active and opcache.save_comments
 * is disabled. The doc comment of a method is then read from the source file
 * that declares the method, so that the types in its @param tags are used
 * regardless of the configuration of OPcache. Each file is parsed only once.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DocComments
{
    /**
     * @var array<string, list<array{name: lowercase-string, startLine: int, endLine: int, docComment: ?string}>>
     */
    private static array $methods = [];

    /**
     * Returns the doc comment of the method, which is read from the source
     * file that declares the method when PHP has discarded it, or false when
     * the method does not have one.
     */
    public static function of(ReflectionMethod $method): false|string
    {
        $docComment = $method->getDocComment();

        if ($docComment !== false || !self::areDiscarded()) {
            return $docComment;
        }

        $docComment = self::inSourceFile($method);

        if ($docComment === null) {
            return false;
        }

        return $docComment;
    }

    public static function areDiscarded(): bool
    {
        if (!(new Runtime)->isOpcacheActive()) {
            return false;
        }

        $saveComments = ini_get('opcache.save_comments');

        // a setting that is turned off in a configuration file is an empty string
        return $saveComments === '0' || $saveComments === '';
    }

    /**
     * Returns the doc comment of the method as it is written in the source
     * file that declares it, or null when the method has none there or the
     * file cannot be read or parsed.
     */
    public static function inSourceFile(ReflectionMethod $method): ?string
    {
        $file = $method->getFileName();
        $line = $method->getStartLine();

        if ($file === false || $line === false) {
            return null;
        }

        if (!isset(self::$methods[$file])) {
            self::$methods[$file] = self::parse($file);
        }

        $name      = strtolower($method->getName());
        $innermost = null;

        foreach (self::$methods[$file] as $declaration) {
            if ($declaration['name'] !== $name || $line < $declaration['startLine'] || $line > $declaration['endLine']) {
                continue;
            }

            // a method of an anonymous class may be declared inside a method
            // of the same name
            if ($innermost === null || $declaration['startLine'] >= $innermost['startLine']) {
                $innermost = $declaration;
            }
        }

        if ($innermost === null) {
            return null;
        }

        return $innermost['docComment'];
    }

    /**
     * @return list<array{name: lowercase-string, startLine: int, endLine: int, docComment: ?string}>
     */
    private static function parse(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $code = file_get_contents($file);

        assert($code !== false);

        try {
            $nodes = (new ParserFactory)->createForHostVersion()->parse($code);
        } catch (Error) {
            // for instance, the file uses syntax of a version of PHP that is
            // newer than the one that the bundled parser supports
            return [];
        }

        if ($nodes === null) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $methods = [];

        foreach ((new NodeFinder)->findInstanceOf($nodes, ClassMethod::class) as $method) {
            $methods[] = [
                'name'       => $method->name->toLowerString(),
                'startLine'  => $method->getStartLine(),
                'endLine'    => $method->getEndLine(),
                'docComment' => $method->getDocComment()?->getText(),
            ];
        }

        return $methods;
    }
}
