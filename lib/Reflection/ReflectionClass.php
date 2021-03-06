<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use Phpactor\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassMethod;
use Phpactor\WorseReflection\ClassName;
use PhpParser\Node\Stmt\Property;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionConstantCollection;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Reflection\AbstractReflectionClass;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Exception\ClassNotFound;
use Phpactor\WorseReflection\Visibility;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionInterfaceCollection;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Position;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\ServiceLocator;

class ReflectionClass extends AbstractReflectionClass
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var ClassLike
     */
    private $node;

    public function __construct(
        ServiceLocator $serviceLocator,
        ClassDeclaration $node
    )
    {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    public function isAbstract(): bool
    {
        $modifier = $this->node->abstractOrFinalModifier;

        if (!$modifier) {
            return false;
        }

        return $modifier->kind === TokenKind::AbstractKeyword;
    }

    public function constants(): ReflectionConstantCollection
    {
        $parentConstants = null;
        if ($this->parent()) {
            $parentConstants = $this->parent()->constants();
        }

        $constants = ReflectionConstantCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentConstants) {
            return $parentConstants->merge($constants);
        }

        return $constants;
    }

    public function parent()
    {
        if (!$this->node->classBaseClause) {
            return;
        }

        try {
            return $this->serviceLocator->reflector()->reflectClass(
                ClassName::fromString((string) $this->node->classBaseClause->baseClass->getResolvedName())
            );
        } catch (ClassNotFound $e) {
        }
    }

    public function properties(): ReflectionPropertyCollection
    {
        $parentProperties = null;
        if ($this->parent()) {
            $parentProperties = $this->parent()->properties()->byVisibilities([ Visibility::public(), Visibility::protected() ]);
        }

        $properties = ReflectionPropertyCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentProperties) {
            return $parentProperties->merge($properties);
        }

        return $properties;
    }

    public function methods(): ReflectionMethodCollection
    {
        $parentMethods = null;
        if ($this->parent()) {
            $parentMethods = $this->parent()->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ]);
        }

        $methods = ReflectionMethodCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentMethods) {
            return $parentMethods->merge($methods);
        }

        return $methods;
    }

    public function interfaces(): ReflectionInterfaceCollection
    {
        return ReflectionInterfaceCollection::fromClassDeclaration($this->serviceLocator, $this->node);
    }

    public function memberListPosition(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node->classMembers->openBrace->fullStart,
            $this->node->classMembers->openBrace->start,
            $this->node->classMembers->openBrace->start + $this->node->classMembers->openBrace->length
        );
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }
}
