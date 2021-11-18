<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Extension;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Configuration\PlatformConfiguration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

final class PlatformExtension extends Extension implements PrependExtensionInterface
{
    public const EXTENSION_ALIAS = 'ezplatform';

    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function getConfiguration(
        array $config,
        ContainerBuilder $container
    ): ConfigurationInterface {
        return new PlatformConfiguration();
    }

    public function getAlias(): string
    {
        return self::EXTENSION_ALIAS;
    }

    public function prepend(ContainerBuilder $container): void
    {
        // inject "ezplatform" extension settings into "ezpublish" extension
        // configuration here is zero-based array of configurations from multiple sources
        // to be merged by "ezpublish" extension
        foreach ($container->getExtensionConfig('ezplatform') as $eZPlatformConfig) {
            $container->prependExtensionConfig('ezpublish', $eZPlatformConfig);
        }
    }
}

class_alias(PlatformExtension::class, 'EzSystems\EzPlatformCoreBundle\DependencyInjection\EzPlatformCoreExtension');
