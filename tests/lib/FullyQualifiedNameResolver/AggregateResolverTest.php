<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\FullyQualifiedNameResolver;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;

/**
 * @covers \Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver
 */
class AggregateResolverTest extends BaseResolverTest
{
    public function getDataForTestResolve(): iterable
    {
        $allResolversTests = [new ClassMapResolverTest(), new PSR4PrefixResolverTest()];

        /** @var \Ibexa\Tests\CompatibilityLayer\FullyQualifiedNameResolver\BaseResolverTest $resolverTest */
        foreach ($allResolversTests as $resolverTest) {
            yield from $resolverTest->getDataForTestResolve();
        }

        yield from [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new AggregateResolver(
            [
                new ClassMapResolver(),
                new PSR4PrefixResolver(),
            ]
        );
    }
}
