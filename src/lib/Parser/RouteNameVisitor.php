<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class RouteNameVisitor extends NodeVisitorAbstract
{
    private array $routeNamesMap;

    public function __construct(array $routeNamesMap)
    {
        $this->routeNamesMap = $routeNamesMap;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Scalar\String_) {
            $newRouteName = $this->getRouteName($node->value);

            if (!empty($newRouteName)) {
                $node->value = $newRouteName;
            }
        }

        return $node;
    }

    public function getRouteName(string $routeName): ?string
    {
        return $this->routeNamesMap[$routeName] ?? null;
    }
}
