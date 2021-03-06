<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\AliasDecoratorCompatibilityPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\AssetThemeCompatibilityPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ContainerParameterCompatibilityPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\FormTypeExtensionCompatibilityPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ServiceCompatibilityPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ServiceTagCompatibilityPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\TwigPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ValueObjectVisitorTagCompatibilityPass;
use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Security\RestSessionBasedFactory;
use Ibexa\CompatibilityLayer\BundleResolver\BundleNameResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Ibexa\CompatibilityLayer\ServiceResolver\ServiceNameResolver;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IbexaCompatibilityLayerBundle extends Bundle
{
    public const MAPPINGS_PATH = __DIR__ . '/Resources/mappings';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $fullyQualifiedNameResolver = new AggregateResolver([
            new ClassMapResolver(),
            new PSR4PrefixResolver(),
        ]);

        $container->addCompilerPass(
            new ServiceCompatibilityPass(
                new AggregateResolver([
                    new ClassMapResolver(true),
                    new PSR4PrefixResolver(true),
                ]),
                new ServiceNameResolver(true)
            ),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            128
        );

        $container->addCompilerPass(
            new AliasDecoratorCompatibilityPass($fullyQualifiedNameResolver),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            127
        );

        $container->addCompilerPass(
            new FormTypeExtensionCompatibilityPass($fullyQualifiedNameResolver),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            -127
        );

        $container->addCompilerPass(
            new ValueObjectVisitorTagCompatibilityPass($fullyQualifiedNameResolver),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            128
        );

        $bundleNameResolver = new BundleNameResolver(true);

        $container->addCompilerPass(
            new TwigPass($bundleNameResolver),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5 //Run after Twig one
        );

        $container->addCompilerPass(
            new AssetThemeCompatibilityPass(),
            PassConfig::TYPE_OPTIMIZE,
            -1
        );

        $container->addCompilerPass(
            new ServiceTagCompatibilityPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            255
        );

        $container->addCompilerPass(
            new ContainerParameterCompatibilityPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            -1000
        );

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $securityExtension */
        $securityExtension = $container->getExtension('security');
        $securityExtension->addSecurityListenerFactory(new RestSessionBasedFactory());
    }
}
