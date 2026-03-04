<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\FullyQualifiedNameResolver;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface
 */
abstract class BaseResolverTest extends TestCase
{
    protected FullyQualifiedNameResolverInterface $resolver;

    /** @return iterable<mixed, array{string, string}> */
    abstract public function getDataForTestResolve(): iterable;

    /**
     * @dataProvider getDataForTestResolve
     */
    final public function testResolve(string $legacyFQN, string $expectedNewFQN): void
    {
        self::assertSame($expectedNewFQN, $this->resolver->resolve($legacyFQN));
    }
}
