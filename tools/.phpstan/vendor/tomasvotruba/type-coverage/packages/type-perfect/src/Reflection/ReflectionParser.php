<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPStan\Reflection\ClassReflection;
use Throwable;

final class ReflectionParser
{
    /**
     * @var array<string, ClassLike>
     */
    private array $classesByFilename = [];

    private readonly Parser $parser;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->createForNewestSupportedVersion();
    }

    public function parseClassReflection(ClassReflection $classReflection): ?ClassLike
    {
        $fileName = $classReflection->getFileName();
        if ($fileName === null) {
            return null;
        }

        return $this->parseFilenameToClass($fileName);
    }

    private function parseFilenameToClass(string $fileName): ClassLike|null
    {
        if (isset($this->classesByFilename[$fileName])) {
            return $this->classesByFilename[$fileName];
        }

        try {
            /** @var string $fileContents */
            $fileContents = file_get_contents($fileName);

            $stmts = $this->parser->parse($fileContents);
            if (! is_array($stmts)) {
                return null;
            }

            // complete namespacedName variables
            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor(new NameResolver());
            $nodeTraverser->traverse($stmts);
        } catch (Throwable) {
            // not reachable
            return null;
        }

        $classLike = $this->findFirstClassLike($stmts);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        $this->classesByFilename[$fileName] = $classLike;

        return $classLike;
    }

    /**
     * @param Node[] $nodes
     */
    private function findFirstClassLike(array $nodes): ?ClassLike
    {
        $nodeFinder = new NodeFinder();
        return $nodeFinder->findFirstInstanceOf($nodes, ClassLike::class);
    }
}
