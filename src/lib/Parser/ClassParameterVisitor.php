<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ClassParameterVisitor extends NodeVisitorAbstract
{
    private array $classParametersMap;

    public function __construct(array $classParametersMap)
    {
        $this->classParametersMap = $classParametersMap;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Scalar\String_) {
            $newClassParameter = $this->getClassParameter($node->value);

            if (!empty($newClassParameter)) {
                $node->value = $newClassParameter;
            }
        }

        return $node;
    }

    public function getClassParameter(string $classParameter): ?string
    {
        return $this->classParametersMap[$classParameter] ?? null;
    }
}
