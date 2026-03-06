<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ParentVisitor extends NodeVisitorAbstract
{
    /** @var list<Node> */
    private array $stack;

    /**
     * @param array<Node> $nodes
     *
     * @return array<Node>|null
     */
    public function beginTraverse(array $nodes): ?array
    {
        $this->stack = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if (!empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }

        $this->stack[] = $node;

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        array_pop($this->stack);

        return null;
    }
}
