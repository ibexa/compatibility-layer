<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer;

interface FullyQualifiedNameResolverInterface
{
    public function resolve(string $fullyQualifiedName): ?string;

    public function getMap(): array;
}
