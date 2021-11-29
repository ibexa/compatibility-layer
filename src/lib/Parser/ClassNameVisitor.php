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
}
