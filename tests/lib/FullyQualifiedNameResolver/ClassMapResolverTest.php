<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\FullyQualifiedNameResolver;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;

/**
 * @covers \Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver
 */
class ClassMapResolverTest extends BaseResolverTest
{
    public function getDataForTestResolve(): iterable
    {
        yield [
            'eZ\Publish\API\Repository\Values\Content\Query\SortClause\DateModified',
            'Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DateModified',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ClassMapResolver();
    }
}
