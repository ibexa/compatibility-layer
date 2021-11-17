<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\FullyQualifiedNameResolver;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;

final class AggregateResolver implements FullyQualifiedNameResolverInterface
{
    /** @var \Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface[]|iterable */
    private $resolvers;

    /**
     * @param \Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface[] $resolvers
     */
    public function __construct(iterable $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolve(string $fullyQualifiedName): ?string
    {
        foreach ($this->resolvers as $resolver) {
            $newName = $resolver->resolve($fullyQualifiedName);
            if (null !== $newName) {
                return $newName;
            }
        }

        return null;
    }
}
