<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\FullyQualifiedNameResolver;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;

/**
 * @covers \Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver
 */
class PSR4PrefixResolverTest extends BaseResolverTest
{
    public function getDataForTestResolve(): iterable
    {
        yield [
            'eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter',
            'Ibexa\Bundle\Core\Routing\DefaultRouter',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new PSR4PrefixResolver();
    }
}
