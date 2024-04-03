#!/usr/bin/env php
<?php declare(strict_types=1);
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

require __DIR__ . '/../../vendor/autoload.php';

foreach ((new FileIteratorFacade)->getFilesAsArray(__DIR__ . '/../../src', '.php') as $file) {
    analyse($file);
}

function analyse(string $file): void
{
    $nodes = parse($file);

    $traverser = new NodeTraverser;

    $traverser->addVisitor(new NameResolver);
    $traverser->addVisitor(new ParentConnectingVisitor);

    $traverser->addVisitor(
        new class extends NodeVisitorAbstract
        {
            public function enterNode(Node $node): void
            {
                if ($node instanceof Interface_ ||
                    $node instanceof Class_ ||
                    $node instanceof Enum_ ||
                    $node instanceof Trait_ ||
                    $node instanceof Function_) {
                    if ($node->getDocComment() !== null &&
                        !str_contains($node->getDocComment()->getText(), '@internal')) {
                        print $node->namespacedName->name . PHP_EOL;
                    }
                }
            }
        }
    );

    $traverser->traverse($nodes);
}

/**
 * @psalm-return array<Node>
 */
function parse(string $file): array
{
    try {
        $nodes = (new ParserFactory)->createForHostVersion()->parse(file_get_contents($file));

        assert($nodes !== null);

        return $nodes;
    } catch (Throwable $t) {
        print $t->getMessage() . PHP_EOL;

        exit(1);
    }
}
