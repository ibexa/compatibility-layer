<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ConfigResolverNamespaceVisitor extends NodeVisitorAbstract
{
    public const CORE_CONFIG_RESOLVER_NAMESPACE = 'ezsettings';

    /** @var array<string, string> */
    private array $namespacesMap;

    /** @var array<string, string> */
    private array $parametersMap;

    public function __construct(array $namespacesMap, array $parametersMap)
    {
        $this->namespacesMap = $namespacesMap;
        // container parameters map for collision checking
        $this->parametersMap = $parametersMap;
    }

    public function leaveNode(Node $node)
    {
        // handle "ezsettings" specifically as that string is quite unique - no extra processing required
        if (
            $node instanceof Node\Scalar\String_ &&
            strpos($node->value, self::CORE_CONFIG_RESOLVER_NAMESPACE) === 0
        ) {
            $node->value = $this->replaceCoreConfigResolverNamespace($node->value);
        } elseif (
            $this->isOneOfMethodCalls(
                $node,
                ContainerParameterVisitor::PARAMETER_RELATED_METHODS
            )
        ) {
            if ($this->isPropertyOrVariableNamed($node, 'configResolver') &&
                $this->hasStringArgumentAtPosition($node, 2)
            ) {
                $value = $node->args[1]->value->value;
                $newNamespaceName = $this->getNewNamespace($value);
                if (!empty($newNamespaceName)) {
                    $node->args[1]->value->value = $newNamespaceName;
                }
            } elseif ($this->isPropertyOrVariableNamed($node, 'container')) {
                if ($this->hasStringArgumentAtPosition($node, 1)) {
                    // direct container parameter operation on config resolver dynamic settings
                    $newParameterName = $this->replaceNamespaceInFullParameter(
                        $node->args[0]->value->value
                    );
                    if (!empty($newParameterName)) {
                        $node->args[0]->value->value = $newParameterName;
                    }
                }
            }
        } elseif ($node instanceof Node\Const_ && $node->value instanceof Node\Scalar\String_) {
            // edge-case: namespace defined in a class constant
            $value = $node->value->value;
            $newNamespaceName = $this->getNewNamespace($value);
            if (!empty($newNamespaceName)) {
                $node->value->value = $newNamespaceName;
            } else {
                // edge-case: full config resolver parameter defined in a const
                $newParameterName = $this->replaceNamespaceInFullParameter($value);
                if (!empty($newParameterName)) {
                    $node->value->value = $newParameterName;
                }
            }
        }

        return $node;
    }

    public function getNewNamespace(string $namespace): ?string
    {
        return $this->namespacesMap[$namespace] ?? null;
    }

    protected function isOneOfMethodCalls(Node $node, array $methodNames): bool
    {
        return $node instanceof Node\Expr\MethodCall &&
            $node->name instanceof Node\Identifier &&
            in_array($node->name->name, $methodNames, true);
    }

    private function isProperty(Node $node, string $variableName): bool
    {
        return $node->var->name instanceof Node\Identifier &&
            $node->var->name->name === $variableName;
    }

    private function isVariable(Node $node, string $variableName): bool
    {
        return is_string($node->var->name) && $node->var->name === $variableName;
    }

    private function hasStringArgumentAtPosition(Node $node, int $position): bool
    {
        return count($node->args) >= $position &&
            $node->args[$position - 1]->value instanceof Node\Scalar\String_;
    }

    protected function isPropertyOrVariableNamed(Node $node, string $name): bool
    {
        return $this->isProperty($node, $name) || $this->isVariable($node, $name);
    }

    private function hasEncapsedArgumentAtPosition(Node $node, int $position): bool
    {
        return count($node->args) >= $position &&
            $node->args[$position - 1]->value instanceof Node\Scalar\Encapsed
            && count($node->args[$position - 1]->value->parts) >= 1;
    }

    private function replaceNamespaceInFullParameter(string $parameterName): ?string
    {
        // make sure it's not actual container parameter with an overlapping naming pattern
        if (in_array($parameterName, $this->parametersMap, true)) {
            return null;
        }

        $parameterParts = explode('.', $parameterName, 3);
        if (count($parameterParts) < 3) {
            return null;
        }
        [$namespace, $scope, $dynamicParameterName] = $parameterParts;

        $newNamespaceName = $this->getNewNamespace($namespace);

        return $newNamespaceName !== null ? "$newNamespaceName.$scope.$dynamicParameterName" : null;
    }

    private function replaceCoreConfigResolverNamespace(string $value): string
    {
        return str_replace(
            self::CORE_CONFIG_RESOLVER_NAMESPACE,
            $this->namespacesMap[self::CORE_CONFIG_RESOLVER_NAMESPACE],
            $value
        );
    }
}
