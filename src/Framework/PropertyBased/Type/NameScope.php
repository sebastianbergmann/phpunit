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
use function get_parent_class;
use function ltrim;
use function str_starts_with;
use function strtolower;
use PhpParser\NameContext;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use ReflectionMethod;

/**
 * Resolves the class names in the PHPDoc types of a method against the
 * namespace and the imports of the file that declares the method, the same
 * way PHPStan does. For a method that is declared in a trait, these are the
 * namespace and the imports of the trait's file, while self and static refer
 * to the class that uses the trait.
 *
 * The file is only parsed when a name is resolved against its namespace and
 * imports, which most PHPDoc types do not require, so that the files of the
 * properties are not parsed while the test suite is built.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class NameScope
{
    private false|string $file;
    private false|int $line;

    /**
     * @var ?class-string
     */
    private ?string $className;

    /**
     * @var ?class-string
     */
    private ?string $parentClassName;

    public static function forMethod(ReflectionMethod $method): self
    {
        $class  = $method->getDeclaringClass();
        $parent = $class->getParentClass();

        $parentClassName = null;

        if ($parent !== false) {
            $parentClassName = $parent->getName();
        }

        return new self(
            $method->getFileName(),
            $method->getStartLine(),
            $class->getName(),
            $parentClassName,
        );
    }

    /**
     * Resolves the class names in a PHPDoc type that is written at the given
     * line of the given file, for instance as the argument of a call, where
     * self and static refer to the given class.
     *
     * @param ?class-string $className
     */
    public static function forCallSite(false|string $file, false|int $line, ?string $className): self
    {
        $parentClassName = null;

        if ($className !== null) {
            $parent = get_parent_class($className);

            if ($parent !== false) {
                $parentClassName = $parent;
            }
        }

        return new self($file, $line, $className, $parentClassName);
    }

    /**
     * @param ?class-string $className
     * @param ?class-string $parentClassName
     */
    private function __construct(false|string $file, false|int $line, ?string $className, ?string $parentClassName)
    {
        $this->file            = $file;
        $this->line            = $line;
        $this->className       = $className;
        $this->parentClassName = $parentClassName;
    }

    /**
     * Resolves a class name as it is written in a PHPDoc type, possibly with a
     * leading backslash, to a fully qualified class name without a leading
     * backslash.
     *
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    public function resolve(string $name): string
    {
        $keyword = strtolower($name);

        if (($keyword === 'self' || $keyword === 'static') && $this->className !== null) {
            return $this->className;
        }

        if ($keyword === 'parent' && $this->parentClassName !== null) {
            return $this->parentClassName;
        }

        if (str_starts_with($name, '\\')) {
            return $this->nonEmpty(ltrim($name, '\\'));
        }

        return $this->nonEmpty($this->context()->getResolvedClassName(new Name($name))->toString());
    }

    /**
     * Returns the shortest name under which the class can be referred to in
     * the file that declares the method: its alias or its namespace-relative
     * name when there is one, and its fully qualified name with a leading
     * backslash otherwise.
     *
     * @param non-empty-string $className a fully qualified class name, possibly with a leading backslash
     *
     * @return non-empty-string
     */
    public function shortName(string $className): string
    {
        $className = $this->nonEmpty(ltrim($className, '\\'));
        $shortName = $this->context()->getShortName($className, Use_::TYPE_NORMAL);

        if ($shortName instanceof FullyQualified) {
            return $this->nonEmpty('\\' . $shortName->toString());
        }

        return $this->nonEmpty($shortName->toString());
    }

    private function context(): NameContext
    {
        return NameContexts::at($this->file, $this->line);
    }

    /**
     * @return non-empty-string
     */
    private function nonEmpty(string $name): string
    {
        assert($name !== '');

        return $name;
    }
}
