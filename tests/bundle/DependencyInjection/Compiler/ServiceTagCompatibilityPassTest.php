<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ServiceTagCompatibilityPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ServiceTagCompatibilityPassTest extends AbstractCompilerPassTestCase
{
    private const SERVICE_ID = 'app.tab.foo';
    private const LEGACY_TAG = 'ezplatform.tab';
    private const IBEXA_TAG = 'ibexa.admin_ui.tab';
    private const TAG_ATTRIBUTES_GROUP_DASHBOARD = ['group' => 'dashboard'];
    private const TAG_ATTRIBUTE_GROUP_LOCATION_VIEW = ['group' => 'location-view'];
    public const ALIAS_ID = 'app.tab.foo.bar';

    public function testProcessTagsServiceTaggedWithLegacyTag(): void
    {
        $definition = new Definition();
        $definition->addTag(self::LEGACY_TAG, self::TAG_ATTRIBUTES_GROUP_DASHBOARD);
        $definition->addTag(self::LEGACY_TAG, self::TAG_ATTRIBUTE_GROUP_LOCATION_VIEW);
        $this->setDefinition(self::SERVICE_ID, $definition);

        $this->compile();

        $this->assertServiceIsTaggedWithIbexaTags(self::SERVICE_ID);

        // for BC reasons the service should still be tagged with legacy tags
        $this->assertServiceIsTaggedWithLegacyTag(self::SERVICE_ID);
    }

    public function testProcessDoesNotTagAlreadyIbexaTaggedService(): void
    {
        $definition = new Definition();
        $definition->addTag(self::LEGACY_TAG, self::TAG_ATTRIBUTES_GROUP_DASHBOARD);
        $definition->addTag(self::IBEXA_TAG, self::TAG_ATTRIBUTES_GROUP_DASHBOARD);
        $this->setDefinition(self::SERVICE_ID, $definition);

        $this->compile();

        self::assertCount(
            1,
            $this->container->findTaggedServiceIds(self::IBEXA_TAG)[self::SERVICE_ID],
            sprintf('Service %s should be tagged only once', self::SERVICE_ID)
        );
    }

    public function testProcessAffectsTaggedServiceAliases(): void
    {
        $definition = new Definition();
        $definition->addTag(self::LEGACY_TAG, self::TAG_ATTRIBUTES_GROUP_DASHBOARD);
        $this->setDefinition(self::SERVICE_ID, $definition);

        $alias = new Alias(self::SERVICE_ID, true);
        $this->container->setAlias(self::ALIAS_ID, $alias);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            self::ALIAS_ID,
            self::IBEXA_TAG,
            self::TAG_ATTRIBUTES_GROUP_DASHBOARD
        );
    }

    protected function assertServiceIsTaggedWithIbexaTags(string $serviceId): void
    {
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            self::IBEXA_TAG,
            self::TAG_ATTRIBUTES_GROUP_DASHBOARD
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            self::IBEXA_TAG,
            self::TAG_ATTRIBUTE_GROUP_LOCATION_VIEW
        );
    }

    protected function assertServiceIsTaggedWithLegacyTag(string $serviceId): void
    {
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            self::LEGACY_TAG,
            self::TAG_ATTRIBUTES_GROUP_DASHBOARD
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            self::LEGACY_TAG,
            self::TAG_ATTRIBUTE_GROUP_LOCATION_VIEW
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ServiceTagCompatibilityPass());
    }
}
