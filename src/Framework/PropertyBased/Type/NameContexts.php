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

use PhpParser\ErrorHandler\Throwing;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;

/**
 * Determines the namespace and the imports that apply to the declaration of
 * a class, an interface, a trait, or an enumeration, the way PHP resolves
 * names. Each file is parsed only once.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class NameContexts
{
    /**
     * @var array<string, list<array{startLine: int, endLine: int, context: NameContext}>>
     */
    private static array $contexts = [];

    /**
     * Returns the name context that applies to the declaration of the class
     * (or interface, trait, or enumeration) that contains the given line, or
     * the context of the global namespace without imports when the file
     * cannot be read or parsed or no declaration contains the line.
     */
    public static function at(false|string $file, false|int $line): NameContext
    {
        if ($file === false || $line === false) {
            return self::globalContext();
        }

        if (!isset(self::$contexts[$file])) {
            self::$contexts[$file] = self::parse($file);
        }

        $innermost = null;

        foreach (self::$contexts[$file] as $declaration) {
            if ($line < $declaration['startLine'] || $line > $declaration['endLine']) {
                continue;
            }

            // an anonymous class may be declared inside the method of another class
            if ($innermost === null || $declaration['startLine'] >= $innermost['startLine']) {
                $innermost = $declaration;
            }
        }

        if ($innermost === null) {
            return self::globalContext();
        }

        return $innermost['context'];
    }

    /**
     * @return list<array{startLine: int, endLine: int, context: NameContext}>
     */
    private static function parse(string $file): array
    {
        $nodes = SourceFileParser::parse($file);

        if ($nodes === null) {
            return [];
        }

        $nameResolver = new NameResolver(null, ['replaceNodes' => false]);

        $collector = new class($nameResolver) extends NodeVisitorAbstract
        {
            /**
             * @var list<array{startLine: int, endLine: int, context: NameContext}>
             */
            public array $declarations = [];
            private readonly NameResolver $nameResolver;

            public function __construct(NameResolver $nameResolver)
            {
                $this->nameResolver = $nameResolver;
            }

            public function enterNode(Node $node): null
            {
                if ($node instanceof ClassLike) {
                    // the name context changes while the remainder of the file
                    // is traversed, so the state that applies to the
                    // declaration is kept as a copy
                    $this->declarations[] = [
                        'startLine' => $node->getStartLine(),
                        'endLine'   => $node->getEndLine(),
                        'context'   => clone $this->nameResolver->getNameContext(),
                    ];
                }

                return null;
            }
        };

        $traverser = new NodeTraverser($nameResolver, $collector);

        $traverser->traverse($nodes);

        return $collector->declarations;
    }

    private static function globalContext(): NameContext
    {
        $context = new NameContext(new Throwing);

        $context->startNamespace();

        return $context;
    }
}
