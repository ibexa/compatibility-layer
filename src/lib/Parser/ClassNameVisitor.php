<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;

class ClassNameVisitor extends RebrandingVisitor
{
    protected array $namespaceAliases = [];

    public function leaveNode(Node $node)
    {
        if ($node instanceof UseUse) {
            if ($node->alias) {
                $this->namespaceAliases[(string)$node->alias->name] = $node->name;
            }
        } else if ($node instanceof Node\Name) {
            return $this->getResolvedNameNode($node);
        }

        return $node;
    }

    private function getResolvedNameNode(Node\Name $name): Node\Name
    {
        /** @var Node\Name $originalNode */
        $originalNode = $name->getAttributes()['origNode'];
        /** @var Node\Name $originalName */
        $originalName = $originalNode->getAttributes()['originalName'] ?? $originalNode;
        $resolvedNode = $this->nameResolver->resolve((string)$originalNode);

        if ($resolvedNode === null) {
            return $name;
        }

        $resolvedNodeParts = explode('\\', $resolvedNode);

        if (count($originalName->parts) === 1 && !isset($this->namespaceAliases[(string)$originalName])) {
            return new Node\Name(end($resolvedNodeParts));
        }

        if (isset($this->namespaceAliases[(string)reset($originalName->parts)])) {
            return $name;
        }

        if ((string)$originalNode === (string)$originalName) {
            if ($originalNode instanceof Node\Name\FullyQualified) {
                return new Node\Name\FullyQualified($resolvedNode);
            }

            return new Node\Name($resolvedNode);
        }

        return new Node\Name($originalName);
    }
}
