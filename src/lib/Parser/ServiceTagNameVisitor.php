<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ServiceTagNameVisitor extends NodeVisitorAbstract
{
    private array $serviceTagNamesMap;

    public function __construct(array $serviceTagNamesMap)
    {
        $this->serviceTagNamesMap = $serviceTagNamesMap;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Scalar\String_) {
            $newServiceTagName = $this->getServiceTagName($node->value);

            if (!empty($newServiceTagName)) {
                $node->value = $newServiceTagName;
            }
        }

        return $node;
    }

    public function getServiceTagName(string $serviceTagName): ?string
    {
        return $this->serviceTagNamesMap[$serviceTagName] ?? null;
    }
}
