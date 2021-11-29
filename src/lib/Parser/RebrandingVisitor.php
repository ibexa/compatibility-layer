<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use PhpParser\NodeVisitorAbstract;

abstract class RebrandingVisitor extends NodeVisitorAbstract
{
    protected FullyQualifiedNameResolverInterface $nameResolver;

    public function __construct(FullyQualifiedNameResolverInterface $nameResolver) {
        $this->nameResolver = $nameResolver;
    }
}
