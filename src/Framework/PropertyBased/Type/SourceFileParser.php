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
use function is_file;
use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

/**
 * Parses the source files from which the doc comments of methods and the
 * namespaces and imports that apply to declarations are read. The nodes of
 * the file that was parsed last are kept, so that a file whose doc comments
 * and namespaces and imports are both needed, one after the other, is parsed
 * once.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SourceFileParser
{
    private static ?string $file = null;

    /**
     * @var ?array<Stmt>
     */
    private static ?array $nodes = null;

    /**
     * Returns the nodes of the file, or null when the file cannot be read or
     * parsed.
     *
     * @return ?array<Stmt>
     */
    public static function parse(string $file): ?array
    {
        if ($file !== self::$file) {
            self::$file  = $file;
            self::$nodes = self::nodes($file);
        }

        return self::$nodes;
    }

    /**
     * @return ?array<Stmt>
     */
    private static function nodes(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }

        $code = file_get_contents($file);

        assert($code !== false);

        try {
            return (new ParserFactory)->createForHostVersion()->parse($code);
        } catch (Error) {
            // for instance, the file uses syntax of a version of PHP that is
            // newer than the one that the bundled parser supports
            return null;
        }
    }
}
