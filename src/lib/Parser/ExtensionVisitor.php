<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ExtensionVisitor extends NodeVisitorAbstract
{
    private array $extensionMap;

    public function __construct(array $extensionMap)
    {
        $this->extensionMap = $extensionMap;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall) {
            if (!$node->name instanceof Node\Identifier) {
                return $node;
            }

            $methodName = (string)$node->name;
            if (in_array($methodName, ['prependExtensionConfig', 'getExtension', 'hasExtension', 'getExtensionConfig'])) {
                if (count($node->args) === 0) {
                    return $node;
                }

                $extension = $node->args[0];
                if ($extension->value instanceof Node\Scalar\String_) {
                    $extensionName = $this->getExtensionName($extension->value->value);
                    if ($extensionName !== null) {
                        $extension->value->value = $extensionName;
                    }
                }

                if (isset($node->args[1]) && $node->args[1]->value instanceof Node\Expr\ArrayDimFetch) {
                    /** @var \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch */
                    $arrayDimFetch = $node->args[1]->value;

                    if ($arrayDimFetch->dim instanceof Node\Scalar\String_) {
                        $extensionName = $this->getExtensionName($arrayDimFetch->dim->value);
                        if ($extensionName !== null) {
                            $arrayDimFetch->dim->value = $extensionName;
                        }
                    }
                }
            }
        }

        return $node;
    }

    public function getExtensionName(string $extension): ?string
    {
        return $this->extensionMap[$extension] ?? null;
    }
}
