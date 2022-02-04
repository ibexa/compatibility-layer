<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\ConfigResolver;

use Ibexa\CompatibilityLayer\ConfigResolver\BackwardCompatibleConfigResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BackwardCompatibleConfigResolverTest extends TestCase
{
    /**
     * See ./src/bundle/Resources/mappings/config-resolver-namespaces-map.php for the full map.
     */
    private const NAMESPACE_MAP = [
        'ezsettings' => 'ibexa.site_access.config',
        'ses_wishlist' => 'ibexa.commerce.site_access.config.wishlist',
    ];

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $chainConfigResolverMock;

    private BackwardCompatibleConfigResolver $resolver;

    public function getDataForTestGetParameter(): iterable
    {
        yield 'description_limit for a default scope in the ses_wishlist namespace' => [
            'description_limit',
            'ses_wishlist',
            null,
            '50',
        ];

        yield 'page_layout for a default scope in a default namespace' => [
            'page_layout',
            null,
            null,
            'page_layout.html.twig',
        ];

        yield 'location_view for a default scope in the ezsettings namespace' => [
            'location_view',
            'ezsettings',
            null,
            'location_view.html.twig',
        ];

        yield 'page_layout for an admin scope in a default namespace' => [
            'page_layout',
            null,
            'admin',
            'page_layout.html.twig',
        ];

        yield 'page_layout for an admin_group scope in the ezsettings namespace' => [
            'page_layout',
            'ezsettings',
            'admin_group',
            'page_layout.html.twig',
        ];

        yield 'page_layout for a site scope in the ibexa.site_access.config namespace' => [
            'page_layout',
            'ibexa.site_access.config',
            'site',
            'page_layout.html.twig',
        ];
    }

    protected function setUp(): void
    {
        $this->chainConfigResolverMock = $this->createMock(ConfigResolverInterface::class);
        $this->resolver = new BackwardCompatibleConfigResolver($this->chainConfigResolverMock);
    }

    /**
     * @dataProvider getDataForTestGetParameter
     */
    public function testGetParameter(
        string $parameterName,
        ?string $namespace,
        ?string $scope,
        string $expectedValue
    ): void {
        $this->configureInnerResolver($parameterName, $namespace, $scope, $expectedValue);

        self::assertSame(
            $expectedValue,
            $this->resolver->getParameter($parameterName, $namespace, $scope)
        );
    }

    /**
     * @dataProvider getDataForTestGetParameter
     */
    public function testHasParameter(
        string $parameterName,
        ?string $namespace,
        ?string $scope
    ): void {
        $this
            ->chainConfigResolverMock
            ->method('hasParameter')
            ->with($parameterName, $namespace, $scope)
            ->willReturn(true);

        self::assertTrue($this->resolver->hasParameter($parameterName, $namespace, $scope));
    }

    private function configureInnerResolver(
        string $parameterName,
        ?string $namespace,
        ?string $scope,
        string $expectedValue
    ): void {
        $resolvedNamespace = self::NAMESPACE_MAP[$namespace] ?? $namespace;
        $isNewNamespace = $namespace === $resolvedNamespace;
        // "hasParameter" call should return false for legacy namespaces and true for the new ones
        $this
            ->chainConfigResolverMock
            ->method('hasParameter')
            ->with($parameterName, $namespace, $scope)
            ->willReturn($isNewNamespace);

        $this
            ->chainConfigResolverMock
            ->method('getParameter')
            ->with($parameterName, $resolvedNamespace, $scope)
            ->willReturn($expectedValue);
    }
}
