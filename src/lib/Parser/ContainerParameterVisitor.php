<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ContainerParameterVisitor extends NodeVisitorAbstract
{
    public const PARAMETER_RELATED_METHODS = ['getParameter', 'hasParameter', 'setParameter'];

    private array $containerParametersMap;

    public function __construct(array $classParametersMap)
    {
        $this->containerParametersMap = $classParametersMap;
    }

    public function leaveNode(Node $node)
    {
        if (
            $node instanceof Node\Scalar\String_ &&
            preg_match('%([a-zA-Z0-9_.-]+)%', $node->value, $matches)
        ) {
            $newParameterName = $this->getNewParameterName($matches[1]);

            if (!empty($newParameterName)) {
                $node->value = "%$newParameterName%";
            }
        } elseif ($node instanceof Node\Expr\MethodCall &&
            $node->name instanceof Node\Identifier &&
            in_array($node->name->name, self::PARAMETER_RELATED_METHODS, true) &&
            count($node->args) >= 1 &&
            $node->args[0]->value instanceof Node\Scalar\String_ &&
            is_string($node->args[0]->value->value)
        ) {
            $newParameterName = $this->getNewParameterName($node->args[0]->value->value);

            if (!empty($newParameterName)) {
                $node->args[0]->value->value = $newParameterName;
            }
        }

        return $node;
    }

    public function getNewParameterName(string $parameterName): ?string
    {
        return $this->containerParametersMap[$parameterName] ?? null;
    }
}
