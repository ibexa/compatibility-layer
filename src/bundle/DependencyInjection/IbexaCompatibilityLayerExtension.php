<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class IbexaCompatibilityLayerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<int, array<string, mixed>> $configs
     *
     * @throws \Exception
     */
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');

        if ($this->areUrlWildcardsEnabled($container)) {
            $loader->load('conditional/url_wildcard.yaml');
        }

        /** @var array<string, mixed> $bundles */
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['IbexaSiteFactoryBundle'])) {
            $loader->load('conditional/site_factory.yaml');
        }
    }

    private function areUrlWildcardsEnabled(ContainerBuilder $container): bool
    {
        if ($container->hasParameter('ibexa.url_wildcards.enabled')) {
            /** @var bool $areUrlWildcardsEnabled */
            $areUrlWildcardsEnabled = $container->getParameter('ibexa.url_wildcards.enabled');

            return $areUrlWildcardsEnabled;
        }

        /** @var bool $areUrlWildcardsEnabled */
        $areUrlWildcardsEnabled = $container->getParameter('ezpublish.url_wildcards.enabled');

        return $areUrlWildcardsEnabled;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependJMSTranslation($container);
    }

    private function prependJMSTranslation(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('jms_translation', [
            'configs' => [
                'ibexa_compatibility_layer' => [
                    'dirs' => [
                        __DIR__ . '/../../',
                    ],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                ],
            ],
        ]);
    }
}
